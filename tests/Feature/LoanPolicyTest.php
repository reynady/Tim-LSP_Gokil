<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanPolicy;
use App\Models\Monster;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_loan_limits(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.loan-policy.edit'))
            ->assertOk()
            ->assertSee('value="2"', false)
            ->assertSee('value="5"', false);

        $this->patch(route('admin.loan-policy.update'), [
            'max_active_loans' => 4,
            'max_duration_days' => 7,
        ])->assertRedirect(route('admin.loan-policy.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('loan_policies', [
            'id' => 1,
            'max_active_loans' => 4,
            'max_duration_days' => 7,
        ]);
    }

    public function test_member_cannot_change_loan_limits(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_active' => true]);

        $this->actingAs($member)
            ->get(route('admin.loan-policy.edit'))
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('admin.loan-policy.update'), [
                'max_active_loans' => 3,
                'max_duration_days' => 8,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('loan_policies', [
            'id' => 1,
            'max_active_loans' => 2,
            'max_duration_days' => 5,
        ]);
    }

    public function test_loan_limits_must_be_positive_integers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->from(route('admin.loan-policy.edit'))
            ->patch(route('admin.loan-policy.update'), [
                'max_active_loans' => 0,
                'max_duration_days' => '1.5',
            ])
            ->assertRedirect(route('admin.loan-policy.edit'))
            ->assertSessionHasErrors(['max_active_loans', 'max_duration_days']);

        $this->assertDatabaseHas('loan_policies', [
            'id' => 1,
            'max_active_loans' => 2,
            'max_duration_days' => 5,
        ]);
    }

    public function test_admin_can_set_limits_above_previous_fixed_maximums(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.loan-policy.update'), [
                'max_active_loans' => 300,
                'max_duration_days' => 365,
            ])
            ->assertRedirect(route('admin.loan-policy.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('loan_policies', [
            'id' => 1,
            'max_active_loans' => 300,
            'max_duration_days' => 365,
        ]);
    }

    public function test_member_pages_use_the_current_policy_values(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_active' => true]);
        Monster::create([
            'monster_code' => 'POLICY-TEST',
            'name' => 'Policy test unit',
            'status' => 'available',
        ]);
        LoanPolicy::current()->update([
            'max_active_loans' => 3,
            'max_duration_days' => 2,
        ]);

        $this->actingAs($member)
            ->get(route('unit.detail'))
            ->assertOk()
            ->assertSee('maksimal 3 peminjaman aktif')
            ->assertSee('maksimal 2 hari (48 jam)')
            ->assertSee('data-max-duration-days="2"', false);

        $this->get(route('loan.active'))
            ->assertOk()
            ->assertSee('Kapasitas maksimal 3 entitas');
    }

    public function test_dashboard_and_success_page_use_policy_and_show_whole_remaining_days(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_active' => true]);
        $monster = Monster::create([
            'monster_code' => 'DASH-POLICY',
            'name' => 'Dashboard policy unit',
            'status' => 'borrowed',
        ]);
        $start = CarbonImmutable::now()->addHour()->startOfMinute();
        $end = $start->addHours(36);
        $loan = Loan::create([
            'loan_code' => 'DASH-POLICY-LOAN',
            'user_id' => $member->id,
            'loan_date' => $start->toDateString(),
            'due_date' => $end->toDateString(),
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'active',
        ]);
        $loan->details()->create(['monster_id' => $monster->id]);
        LoanPolicy::current()->update([
            'max_active_loans' => 4,
            'max_duration_days' => 2,
        ]);

        $dashboard = $this->actingAs($member)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kapasitas tersedia · 1 dari 4 monster dipinjam');

        $this->assertMatchesRegularExpression('/2 hari lagi/', $dashboard->getContent());
        $this->assertDoesNotMatchRegularExpression('/\\d+\\.\\d+ hari lagi/', $dashboard->getContent());

        $this->get(route('loan.success', ['code' => $loan->loan_code]))
            ->assertOk()
            ->assertSee('Kapasitas tersedia · 1 dari 4 unit')
            ->assertSee('Sisa kuota 3 entitas monster');
    }
}
