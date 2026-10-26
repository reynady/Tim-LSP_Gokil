<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Fine;
use App\Models\Loan;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MonsterSeeder;
use Database\Seeders\MemberSeeder;
use Database\Seeders\OverdueLoanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FineOverdueTest extends TestCase
{
    use RefreshDatabase;

    public function test_fine_starts_after_five_grace_days(): void
    {
        $this->assertSame(0, Fine::amountForOverdueDays(5));
        $this->assertSame(25, Fine::amountForOverdueDays(6));
        $this->assertSame(50, Fine::amountForOverdueDays(7));
    }

    public function test_admin_can_access_keterlambatan_denda_page(): void
    {
        $this->seed([
            AdminSeeder::class,
            CategorySeeder::class,
            MonsterSeeder::class,
            MemberSeeder::class,
            OverdueLoanSeeder::class,
        ]);

        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.fines.index'));

        $response->assertStatus(200);
        $response->assertSee('Keterlambatan &amp; denda', false);
        $response->assertSee('Tarif denda keterlambatan');
        $response->assertSee('25 Ether per hari');
        $response->assertSee('Aktif terlambat');
        $response->assertSee('Keterlambatan berjalan');
        $response->assertSee('Sudah kembali terlambat');
        $response->assertSee('Pinjaman aktif yang terlambat');
        $response->assertSee('Sudah dikembalikan · keterlambatan tersimpan');
        $response->assertSee('AT-PJM-261017-012');
        $response->assertSee('Nadia Putri');
        $response->assertSee('AT-SM-006');
        $response->assertSee('Simulator latihan C');
        $response->assertSee('Proses pengembalian');
        $response->assertSee('AT-PJM-261008-007');
        $response->assertSee('Selesai');
        $response->assertSee('AT-PJM-DEMO-FINE-001');
        $response->assertSee('50 Ether');

        $member = User::where('email', 'raka.pratama@arsenaltrack.test')->firstOrFail();
        $historyResponse = $this->actingAs($member)->get(route('loan.history'));

        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('AT-PJM-DEMO-FINE-001');
        $historyResponse->assertSee('50 Ether');
    }

    public function test_returning_a_loan_six_days_late_records_an_ether_fine(): void
    {
        $this->seed([
            AdminSeeder::class,
            CategorySeeder::class,
            MonsterSeeder::class,
            MemberSeeder::class,
            OverdueLoanSeeder::class,
        ]);

        $admin = User::where('role', 'admin')->firstOrFail();
        $loan = Loan::where('loan_code', 'AT-PJM-261017-012')->firstOrFail();
        $loan->update(['due_date' => now()->subDays(6)->toDateString()]);

        $response = $this->actingAs($admin)->post(route('admin.loans.process-return', $loan), [
            'confirm_physical' => '1',
        ]);

        $response->assertRedirect(route('admin.loans.index'));
        $this->assertDatabaseHas('fines', [
            'loan_detail_id' => $loan->details()->firstOrFail()->id,
            'amount' => 25,
            'overdue_days' => 6,
            'status' => 'unpaid',
        ]);
    }
}
