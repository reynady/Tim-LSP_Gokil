<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use RefreshDatabase;

    private const MESSAGE = 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.';

    public function test_inactive_authenticated_member_is_logged_out_and_session_is_invalidated(): void
    {
        $member = $this->member(false);
        $this->withSession(['private-data' => 'must disappear']);
        $oldId = app('session.store')->getId();
        $oldToken = app('session.store')->token();

        $this->actingAs($member)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => self::MESSAGE])
            ->assertSessionMissing('private-data');

        $this->assertGuest();
        $this->assertNotSame($oldId, app('session.store')->getId());
        $this->assertNotSame($oldToken, app('session.store')->token());
    }

    public function test_inactive_authenticated_json_request_is_forbidden_and_logged_out(): void
    {
        $this->actingAs($this->member(false))
            ->getJson(route('dashboard'))
            ->assertForbidden()->assertExactJson(['message' => self::MESSAGE]);

        $this->assertGuest();
    }

    public function test_inactive_member_cannot_mutate_profile(): void
    {
        $member = $this->member(false);

        $this->actingAs($member)->post(route('profile.update'), ['name' => 'Forbidden change'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => self::MESSAGE]);

        $this->assertSame($member->name, $member->fresh()->name);
        $this->assertGuest();
    }

    public function test_inactive_login_is_rejected_and_reactivated_member_can_login(): void
    {
        $member = $this->member(false);

        $this->from(route('login'))->post(route('login.process'), [
            'email' => $member->email, 'password' => 'password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => self::MESSAGE]);
        $this->assertGuest();

        $member->update(['is_active' => true]);
        app('auth')->forgetGuards();

        $this->post(route('login.process'), [
            'email' => $member->email, 'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($member);
    }

    public function test_wrong_password_does_not_disclose_inactive_status(): void
    {
        $member = $this->member(false);

        $this->from(route('login'))->post(route('login.process'), [
            'email' => $member->email, 'password' => 'incorrect-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    public static function deactivationActions(): array
    {
        return [['toggle'], ['update'], ['archive']];
    }

    #[DataProvider('deactivationActions')]
    public function test_deactivation_revokes_only_target_sessions_and_remember_token(string $action): void
    {
        $this->useDatabaseSessions();
        $admin = $this->admin();
        $member = $this->member();
        $other = $this->member();
        $ids = [$this->seedSession($member), $this->seedSession($member)];
        $otherId = $this->seedSession($other);
        $adminId = $this->seedSession($admin);

        $this->actingAs($admin);
        $this->deactivate($member, $action)
            ->assertRedirect(route('admin.members.index'))->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->is_active);
        $this->assertNull($member->fresh()->remember_token);
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('sessions', ['id' => $id]);
            $this->assertTrue(Cache::has($this->revokedKey($id)));
        }
        foreach ([$otherId, $adminId] as $id) {
            $this->assertDatabaseHas('sessions', ['id' => $id]);
            $this->assertFalse(Cache::has($this->revokedKey($id)));
        }
        $this->assertNotNull($other->fresh()->remember_token);
        $this->assertNotNull($admin->fresh()->remember_token);
        if ($action === 'archive') {
            $this->assertDatabaseHas('loans', ['user_id' => $member->id, 'status' => 'returned']);
        }
    }

    public static function responseFormats(): array
    {
        return [['html'], ['json']];
    }

    #[DataProvider('responseFormats')]
    public function test_revoked_database_session_cookie_is_rejected_before_authentication(string $format): void
    {
        $this->useDatabaseSessions();
        $member = $this->member();
        $this->post(route('login.process'), [
            'email' => $member->email, 'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $id = app('session.store')->getId();
        $this->assertDatabaseHas('sessions', ['id' => $id, 'user_id' => $member->id]);

        // Prove the encrypted browser cookie authenticates before revocation.
        $this->freshBrowserSession($id);
        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($member);

        // Isolate the admin's browser and guard from the member's session.
        $this->freshBrowserSession(Str::random(40));
        $this->actingAs($this->admin())
            ->patch(route('admin.members.toggle-status', $member))
            ->assertRedirect(route('admin.members.index'));
        $this->assertDatabaseMissing('sessions', ['id' => $id]);

        $this->freshBrowserSession($id);
        if ($format === 'json') {
            $this->withHeader('Accept', 'application/json')->get(route('dashboard'))
                ->assertForbidden()->assertExactJson(['message' => self::MESSAGE]);
        } else {
            $this->get(route('dashboard'))->assertRedirect(route('login'))
                ->assertSessionHasErrors(['email' => self::MESSAGE]);
            $this->freshBrowserSession(app('session.store')->getId());
            $this->get(route('login'))->assertOk()->assertSee(self::MESSAGE);
        }
        $this->assertGuest();
    }

    public function test_revocation_markers_expire_after_session_lifetime_plus_five_minutes(): void
    {
        $this->useDatabaseSessions();
        config(['session.lifetime' => 10]);
        $this->freezeTime();
        $member = $this->member();
        $id = $this->seedSession($member);

        $member->revokeSessions();

        $this->travel(14)->minutes();
        $this->assertTrue(Cache::has($this->revokedKey($id)));
        $this->travel(2)->minutes();
        $this->assertFalse(Cache::has($this->revokedKey($id)));
    }

    public function test_revoke_sessions_uses_configured_database_connection_and_table(): void
    {
        $this->useDatabaseSessions();
        Schema::rename('sessions', 'account_sessions');
        config(['session.connection' => 'sqlite', 'session.table' => 'account_sessions']);
        $member = $this->member();
        $other = $this->member();
        $id = $this->seedSession($member);
        $otherId = $this->seedSession($other);

        $member->revokeSessions();

        $this->assertDatabaseMissing('account_sessions', ['id' => $id], 'sqlite');
        $this->assertDatabaseHas('account_sessions', ['id' => $otherId], 'sqlite');
        $this->assertTrue(Cache::has($this->revokedKey($id)));
        $this->assertNull($member->fresh()->remember_token);
    }

    public function test_non_database_driver_resets_remember_token_without_deleting_database_sessions(): void
    {
        config(['session.driver' => 'array']);
        $member = $this->member();
        $id = $this->seedSession($member);

        $member->revokeSessions();

        $this->assertNull($member->fresh()->remember_token);
        $this->assertDatabaseHas('sessions', ['id' => $id]);
        $this->assertFalse(Cache::has($this->revokedKey($id)));
    }

    public function test_reactivation_does_not_revoke_sessions_or_reset_remember_token(): void
    {
        $this->useDatabaseSessions();
        $member = $this->member(false);
        $id = $this->seedSession($member);
        $token = $member->remember_token;

        $this->actingAs($this->admin())->patch(route('admin.members.toggle-status', $member))
            ->assertRedirect(route('admin.members.index'));

        $this->assertTrue($member->fresh()->is_active);
        $this->assertSame($token, $member->fresh()->remember_token);
        $this->assertDatabaseHas('sessions', ['id' => $id]);
        $this->assertFalse(Cache::has($this->revokedKey($id)));
    }

    public static function protectedTargets(): array
    {
        return [
            ['toggle', false], ['update', false], ['archive', false],
            ['toggle', true], ['update', true], ['archive', true],
        ];
    }

    #[DataProvider('protectedTargets')]
    public function test_admin_accounts_and_self_cannot_be_deactivated(string $action, bool $self): void
    {
        $admin = $this->admin();
        $target = $self ? $admin : $this->admin();
        $token = $target->remember_token;
        $this->actingAs($admin);

        $this->deactivate($target, $action)->assertNotFound();

        $this->assertTrue($target->fresh()->is_active);
        $this->assertSame($token, $target->fresh()->remember_token);
    }

    private function member(bool $active = true): User
    {
        return User::factory()->create(['role' => 'member', 'is_active' => $active]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function useDatabaseSessions(): void
    {
        config(['session.driver' => 'database', 'session.connection' => 'sqlite',
            'session.table' => 'sessions', 'session.lottery' => [0, 100]]);
        app('session')->forgetDrivers();
        app()->forgetInstance('session.store');
    }

    private function seedSession(User $user): string
    {
        $id = Str::random(40);
        DB::connection(config('session.connection'))->table(config('session.table'))->insert([
            'id' => $id,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'InactiveAccountTest',
            'payload' => base64_encode(serialize([
                app('auth')->guard('web')->getName() => $user->id,
                '_token' => Str::random(40),
            ])),
            'last_activity' => now()->timestamp,
        ]);

        return $id;
    }

    private function freshBrowserSession(string $id): void
    {
        app('auth')->forgetGuards();
        app('session')->forgetDrivers();
        app()->forgetInstance('session.store');
        // The redirector retains its injected store; rebuild it for this browser too.
        app()->forgetInstance('redirect');
        // withCookie encrypts this plain session ID just like a browser cookie.
        $this->withCookie(config('session.cookie'), $id);
    }

    private function revokedKey(string $id): string
    {
        return 'revoked-session:'.hash('sha256', $id);
    }

    private function deactivate(User $member, string $action): TestResponse
    {
        if ($action === 'update') {
            return $this->put(route('admin.members.update', $member), [
                'name' => $member->name, 'email' => $member->email, 'is_active' => '0',
            ]);
        }
        if ($action === 'archive') {
            DB::table('loans')->insert([
                'loan_code' => 'TEST-'.Str::uuid(), 'user_id' => $member->id,
                'loan_date' => today(), 'due_date' => today()->addDays(7),
                'status' => 'returned', 'created_at' => now(), 'updated_at' => now(),
            ]);

            return $this->delete(route('admin.members.destroy', $member));
        }

        return $this->patch(route('admin.members.toggle-status', $member));
    }
}
