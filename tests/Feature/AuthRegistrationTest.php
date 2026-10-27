<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_registration_creates_a_pending_account_that_cannot_login(): void
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
        $response->assertSessionHas('status', 'Pendaftaran berhasil. Akun menunggu validasi admin; kamu bisa masuk setelah disetujui.');
        $this->assertGuest();

        $user = User::where('email', 'anggota@example.test')->firstOrFail();
        $this->assertSame('member', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $user->approval_status);
        $this->assertTrue(password_verify('SecurePass123', $user->password));

        $loginPage = $this->get(route('login'))->assertOk();
        $csrfToken = session()->token();

        $this->from(route('login'))->post(route('login.process'), [
            'email' => 'anggota@example.test',
            'password' => 'SecurePass123',
            '_token' => $csrfToken,
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun masih menunggu persetujuan admin.']);
        $this->assertNotSame($csrfToken, session()->token());
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token" value="'.session()->token().'"', false);
        $this->assertGuest();
    }

    public function test_admin_approval_activates_account_and_allows_login(): void
    {
        $this->post(route('register.process'), [
            'name' => 'Anggota Menunggu',
            'email' => 'pending@example.test',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $user = User::where('email', 'pending@example.test')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.members.review-registration', $user), ['decision' => 'approve'])
            ->assertRedirect(route('admin.members.index'))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['status' => 'pending']))
            ->assertOk()
            ->assertDontSee('pending@example.test');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        auth()->logout();
        $this->post(route('login.process'), [
            'email' => 'pending@example.test',
            'password' => 'SecurePass123',
        ])->assertRedirect('/dashboard');
    }

    public function test_admin_can_reject_then_reapprove_registration(): void
    {
        $this->post(route('register.process'), [
            'name' => 'Anggota Ditolak',
            'email' => 'rejected@example.test',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $user = User::where('email', 'rejected@example.test')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.members.review-registration', $user), ['decision' => 'reject'])
            ->assertRedirect(route('admin.members.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'approval_status' => 'rejected',
            'is_active' => false,
        ]);

        auth()->logout();
        $this->post(route('login.process'), [
            'email' => 'rejected@example.test',
            'password' => 'SecurePass123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)
            ->patch(route('admin.members.review-registration', $user), ['decision' => 'approve'])
            ->assertRedirect(route('admin.members.index'))
            ->assertSessionHas('success', "Pendaftaran '{$user->name}' disetujui. Anggota sekarang dapat login.");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        auth()->logout();
        $this->post(route('login.process'), [
            'email' => 'rejected@example.test',
            'password' => 'SecurePass123',
        ])->assertRedirect('/dashboard');
    }

    public function test_non_admin_cannot_review_registration(): void
    {
        $this->post(route('register.process'), [
            'name' => 'Anggota Menunggu',
            'email' => 'waiting@example.test',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);
        $user = User::where('email', 'waiting@example.test')->firstOrFail();
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->patch(route('admin.members.review-registration', $user), ['decision' => 'approve'])
            ->assertForbidden();

        $this->assertSame('pending', $user->fresh()->approval_status);
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_member_pagination_uses_the_compact_admin_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->count(11)->create(['role' => 'member']);

        $this->actingAs($admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee('class="admin-pagination"', false)
            ->assertSee('class="pagination-links"', false)
            ->assertDontSee('w-5 h-5', false);
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
