<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_an_active_member_account(): void
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
        $response->assertSessionHas('status');
        $this->assertGuest();

        $user = User::where('email', 'anggota@example.test')->firstOrFail();
        $this->assertSame('member', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(password_verify('SecurePass123', $user->password));

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
