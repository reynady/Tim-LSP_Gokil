<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_member_must_be_approved_by_admin_before_login(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun baru.')
            ->assertSee('Konfirmasi kata sandi');

        $response = $this->post(route('register.process'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota@example.test',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Pendaftaran berhasil. Akun kamu menunggu persetujuan admin sebelum bisa digunakan.');
        $this->assertGuest();

        $user = User::where('email', 'anggota@example.test')->firstOrFail();
        $this->assertSame('member', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertNull($user->approved_at);
        $this->assertTrue(password_verify('SecurePass123', $user->password));

        $this->from(route('login'))->post(route('login.process'), [
            'email' => 'anggota@example.test',
            'password' => 'SecurePass123',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Pendaftaran kamu masih menunggu persetujuan admin.']);
        $this->assertGuest();

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->get(route('admin.members.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Menunggu Persetujuan')
            ->assertSee('anggota@example.test');

        $this->actingAs($admin)
            ->patch(route('admin.members.toggle-status', $user))
            ->assertRedirect(route('admin.members.index'));

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->approved_at);

        $this->post(route('login.process'), [
            'email' => 'anggota@example.test',
            'password' => 'SecurePass123',
        ])->assertRedirect('/dashboard');
    }

    public function test_registration_rejects_duplicate_email_and_mismatched_password(): void
    {
        User::factory()->create(['email' => 'used@example.test']);

        $this->from(route('register'))
            ->post(route('register.process'), [
                'name' => 'Anggota Baru',
                'email' => 'used@example.test',
                'password' => 'SecurePass123',
                'password_confirmation' => 'DifferentPass123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertSame(1, User::where('email', 'used@example.test')->count());
    }
}
