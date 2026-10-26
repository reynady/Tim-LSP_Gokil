<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MonsterSeeder;
use Database\Seeders\MemberSeeder;
use Database\Seeders\OverdueLoanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            AdminSeeder::class,
            CategorySeeder::class,
            MonsterSeeder::class,
            MemberSeeder::class,
            OverdueLoanSeeder::class,
        ]);
    }

    public function test_admin_can_access_history_and_print_history(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Admin access history page
        $resHistory = $this->actingAs($admin)->get(route('admin.loans.history'));
        $resHistory->assertStatus(200);
        $resHistory->assertSee('Riwayat Sirkulasi Peminjaman');
        $resHistory->assertSee('Cetak Riwayat');

        // 2. Admin access print preview page
        $resPrint = $this->actingAs($admin)->get(route('admin.loans.print'));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Pratinjau cetak');
        $resPrint->assertSee('Cetak dokumen');
        $resPrint->assertSee('Laporan Riwayat Peminjaman Unit');
        $resPrint->assertSee('ArsenalTrack');
        $resPrint->assertSee('Seluruh Transaksi Sirkulasi Institusi');
        $resPrint->assertSee('Total transaksi');
        $resPrint->assertSee('Selesai tepat waktu');
        $resPrint->assertSee('Selesai terlambat');
    }

    public function test_member_can_access_history_and_print_history(): void
    {
        $member = User::where('role', 'member')->first();

        // 1. Member access history page
        $resHistory = $this->actingAs($member)->get(route('loan.history'));
        $resHistory->assertStatus(200);
        $resHistory->assertSee('Cetak Riwayat');

        // 2. Member access print preview page
        $resPrint = $this->actingAs($member)->get(route('loan.history.print'));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Pratinjau cetak');
        $resPrint->assertSee('Cetak dokumen');
        $resPrint->assertSee('Laporan Riwayat Peminjaman Unit');
        $resPrint->assertSee('ArsenalTrack');
        $resPrint->assertSee('Total transaksi');
    }
}
