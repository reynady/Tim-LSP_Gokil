<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMemberEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_edit_displays_member_and_profile_data(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_active' => false]);
        $member->profile()->create(['phone' => '081234567890', 'address' => 'Alamat lama']);

        $this->get(route('admin.members.edit', $member))
            ->assertOk()->assertViewIs('admin.members.edit')
            ->assertSee($member->name)->assertSee($member->email)
            ->assertSee('081234567890')->assertSee('Alamat lama')
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('value="0" selected', false);
        $this->get(route('admin.members.index'))->assertOk();
        $this->get(route('admin.members.create'))->assertOk();
    }

    public function test_update_preserves_empty_password_and_updates_profile(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $member->profile()->create(['phone' => '081111111111']);
        $password = $member->password;

        $this->put(route('admin.members.update', $member), [
            'name' => 'Nama Baru', 'email' => $member->email,
            'password' => '', 'phone' => '082222222222',
            'address' => 'Alamat baru', 'is_active' => '0',
        ])->assertRedirect(route('admin.members.index'))
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame($password, $member->fresh()->password);
        $this->assertSame('Nama Baru', $member->fresh()->name);
        $this->assertFalse($member->fresh()->is_active);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $member->id, 'phone' => '082222222222', 'address' => 'Alamat baru',
        ]);
        $this->assertDatabaseCount('profiles', 1);
    }

    public function test_password_can_change_and_missing_profile_is_created(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->get(route('admin.members.edit', $member))->assertOk();

        $this->put(route('admin.members.update', $member), [
            'name' => $member->name, 'email' => $member->email,
            'password' => 'new-password-123', 'phone' => '081234567890', 'is_active' => '1',
        ])->assertRedirect(route('admin.members.index'))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $member->fresh()->password));
        $this->assertDatabaseHas('profiles', ['user_id' => $member->id, 'phone' => '081234567890']);
    }

    public function test_duplicate_email_is_rejected_and_old_input_is_shown(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $other = User::factory()->create();

        $this->from(route('admin.members.edit', $member))
            ->put(route('admin.members.update', $member), [
                'name' => 'Nama percobaan', 'email' => $other->email,
                'phone' => '089999999999', 'address' => 'Alamat percobaan', 'is_active' => '0',
            ])->assertRedirect(route('admin.members.edit', $member))->assertSessionHasErrors('email');

        $this->get(route('admin.members.edit', $member))->assertOk()
            ->assertSee('Nama percobaan')->assertSee('089999999999')
            ->assertSee('Alamat percobaan')->assertSee('Email sudah digunakan oleh pengguna lain')
            ->assertSee('value="0" selected', false);
        $this->assertNotSame($other->email, $member->fresh()->email);
    }

    public function test_destroy_deletes_member_without_loans(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $member->profile()->create(['phone' => '081234567890']);

        $this->delete(route('admin.members.destroy', $member))
            ->assertRedirect(route('admin.members.index'))->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('profiles', ['user_id' => $member->id]);
    }
}
