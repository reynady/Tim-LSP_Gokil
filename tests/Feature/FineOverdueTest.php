<?php

namespace Tests\Feature;

use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MemberSeeder;
use Database\Seeders\MonsterSeeder;
use Database\Seeders\OverdueLoanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FineOverdueTest extends TestCase
{
    use RefreshDatabase;

    public function test_fine_is_charged_for_each_overdue_day(): void
    {
        $this->assertSame(0, Fine::amountForOverdueDays(0));
        $this->assertSame(25_000, Fine::amountForOverdueDays(1));
        $this->assertSame(125_000, Fine::amountForOverdueDays(5));
        $this->assertSame(150_000, Fine::amountForOverdueDays(6));
        $this->assertSame(175_000, Fine::amountForOverdueDays(7));
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
        $response->assertSee('Rp25.000 per hari keterlambatan');
        $response->assertSee('Lihat nilai');
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
        $response->assertSee('Rp175.000');

        $member = User::where('email', 'raka.pratama@arsenaltrack.test')->firstOrFail();
        $historyResponse = $this->actingAs($member)->get(route('loan.history'));

        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('AT-PJM-DEMO-FINE-001');
        $historyResponse->assertSee('Rp175.000');
        $historyResponse->assertSee('Lihat nilai');
    }

    public function test_returning_a_loan_one_day_late_records_a_rupiah_fine(): void
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
        $loan->update(['due_date' => now()->subDay()->toDateString()]);

        $returnPage = $this->actingAs($admin)->get(route('admin.loans.return', $loan));
        $returnPage->assertOk()
            ->assertSee('confirm_fine_paid')
            ->assertSee('sudah dibayar');

        $response = $this->actingAs($admin)->post(route('admin.loans.process-return', $loan), [
            'confirm_physical' => '1',
        ]);

        $response->assertSessionHasErrors('confirm_fine_paid');
        $this->assertSame('borrowed', $loan->fresh()->status);

        $response = $this->actingAs($admin)->post(route('admin.loans.process-return', $loan), [
            'confirm_physical' => '1',
            'confirm_fine_paid' => '1',
        ]);

        $response->assertRedirect(route('admin.loans.index'));
        $this->assertDatabaseHas('fines', [
            'loan_detail_id' => $loan->details()->firstOrFail()->id,
            'amount' => 25000,
            'overdue_days' => 1,
            'status' => 'paid',
        ]);
        $this->assertNotNull(Fine::where('loan_detail_id', $loan->details()->firstOrFail()->id)->value('paid_at'));
    }

    public function test_currency_migration_converts_existing_fines_to_rupiah(): void
    {
        $this->seed([
            AdminSeeder::class,
            CategorySeeder::class,
            MonsterSeeder::class,
            MemberSeeder::class,
            OverdueLoanSeeder::class,
        ]);

        $fine = Fine::where('overdue_days', 7)->firstOrFail();
        $fine->update(['amount' => 175]);

        $migration = require database_path('migrations/2026_10_27_110000_convert_fine_amounts_to_rupiah.php');
        $migration->up();

        $this->assertDatabaseHas('fines', [
            'id' => $fine->id,
            'amount' => 175_000,
        ]);
    }
}
