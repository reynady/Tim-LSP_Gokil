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

class FineOverdueTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertSee('Kebijakan denda belum ditetapkan');
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
    }
}
