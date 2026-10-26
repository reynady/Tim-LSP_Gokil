<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Monster;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoanScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Monster $monster;

    protected function beforeRefreshingDatabase(): void
    {
        // Fail before RefreshDatabase can migrate any persistent database.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-26 08:00:00', 'Asia/Jakarta'));
        $this->member = User::factory()->create(['role' => 'member', 'is_active' => true]);
        $this->monster = $this->makeMonster();
    }

    private function makeMonster(string $status = 'available'): Monster
    {
        return Monster::create([
            'monster_code' => 'SCHEDULE-'.Str::ulid(),
            'name' => 'Schedule test unit',
            'status' => $status,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'monster_id' => $this->monster->id,
            'starts_at' => '2026-10-27T10:15',
            'ends_at' => '2026-10-27T12:45',
        ], $overrides);
    }

    private function reservation(
        string $start = '2026-10-27T10:15',
        string $end = '2026-10-27T12:45',
        string $status = 'active',
        ?User $user = null,
        ?Monster $monster = null,
        bool $returnedDetail = false,
    ): Loan {
        $start = CarbonImmutable::parse($start, 'Asia/Jakarta');
        $end = CarbonImmutable::parse($end, 'Asia/Jakarta');
        $loan = Loan::create([
            'loan_code' => 'TEST-'.Str::ulid(),
            'user_id' => ($user ?? User::factory()->create(['role' => 'member']))->id,
            'loan_date' => $start->toDateString(),
            'due_date' => $end->toDateString(),
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => $status,
        ]);
        $loan->details()->create([
            'monster_id' => ($monster ?? $this->monster)->id,
            'returned_at' => $returnedDetail ? now() : null,
        ]);

        return $loan;
    }

    private function available(string $start, string $end, ?Monster $monster = null): int
    {
        return ($monster ?? $this->monster)->fresh()->availableUnitsDuring(
            CarbonImmutable::parse($start, 'Asia/Jakarta'),
            CarbonImmutable::parse($end, 'Asia/Jakarta'),
        );
    }

    public function test_availability_route_has_the_required_name_path_and_get_method(): void
    {
        $route = Route::getRoutes()->getByName('loan.availability');

        $this->assertNotNull($route, 'Register the loan.availability route.');
        $this->assertSame('peminjaman/ketersediaan', $route->uri());
        $this->assertContains('GET', $route->methods());
    }

    #[DataProvider('endpoints')]
    public function test_guests_cannot_access_schedule_endpoints(string $endpoint): void
    {
        $response = $endpoint === 'store'
            ? $this->post(route('loan.store'), $this->payload())
            : $this->get('/peminjaman/ketersediaan?'.http_build_query($this->payload()));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseCount('loan_details', 0);
    }

    #[DataProvider('endpoints')]
    public function test_admin_cannot_use_member_schedule_endpoints(string $endpoint): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $response = $endpoint === 'store'
            ? $this->postJson(route('loan.store'), $this->payload())
            : $this->getJson('/peminjaman/ketersediaan?'.http_build_query($this->payload()));

        $response->assertForbidden();
        $this->assertDatabaseCount('loans', 0);
    }

    public static function endpoints(): array
    {
        return ['store' => ['store'], 'availability' => ['availability']];
    }

    #[DataProvider('validSchedules')]
    public function test_member_can_save_a_valid_wib_schedule(string $start, string $end): void
    {
        $response = $this->actingAs($this->member)->post(route('loan.store'), $this->payload([
            'starts_at' => $start,
            'ends_at' => $end,
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('loans', 1);
        $this->assertDatabaseCount('loan_details', 1);
        $loan = Loan::sole();
        $response->assertRedirect(route('loan.success', ['code' => $loan->loan_code]));
        $this->assertSame($this->member->id, $loan->user_id);
        $this->assertSame('active', $loan->status);
        $this->assertSame('borrowed', $this->monster->fresh()->status);
        $this->assertSame($this->monster->id, $loan->details->sole()->monster_id);
        $this->assertNull($loan->details->sole()->returned_at);
        $this->assertSame(substr($start, 0, 10), $loan->loan_date->toDateString());
        $this->assertSame(substr($end, 0, 10), $loan->due_date->toDateString());
        $this->assertInstanceOf(CarbonImmutable::class, $loan->starts_at);
        $this->assertInstanceOf(CarbonImmutable::class, $loan->ends_at);
        $this->assertSame($start, $loan->starts_at->format('Y-m-d\TH:i'));
        $this->assertSame($end, $loan->ends_at->format('Y-m-d\TH:i'));
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame(7 * 3600, $loan->starts_at->utcOffset() * 60);
        $this->assertTrue($loan->scheduled_start->equalTo($loan->starts_at));
        $this->assertTrue($loan->scheduled_end->equalTo($loan->ends_at));
        $this->assertFalse($loan->starts_at->addHour()->equalTo($loan->starts_at));
    }

    public static function validSchedules(): array
    {
        return [
            'starts exactly now' => ['2026-10-26T08:00', '2026-10-26T08:01'],
            'same day with minutes' => ['2026-10-27T10:15', '2026-10-27T12:45'],
            'crosses WIB midnight' => ['2026-10-27T23:30', '2026-10-28T00:15'],
            'exactly 120 hours' => ['2026-10-27T10:15', '2026-11-01T10:15'],
            'future start has no five day lead time limit' => ['2026-12-10T10:15', '2026-12-10T12:45'],
        ];
    }

    #[DataProvider('invalidSchedules')]
    public function test_invalid_schedules_are_rejected_without_writes(string $endpoint, array $changes, array $errors): void
    {
        $payload = $changes === [] ? [] : $this->payload($changes);
        $this->actingAs($this->member);
        $response = $endpoint === 'store'
            ? $this->postJson(route('loan.store'), $payload)
            : $this->getJson('/peminjaman/ketersediaan?'.http_build_query($payload));

        $response->assertUnprocessable()->assertJsonValidationErrors($errors);
        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseCount('loan_details', 0);
        $this->assertSame('available', $this->monster->fresh()->status);
    }

    public static function invalidSchedules(): iterable
    {
        $cases = [
            'missing all' => [[], ['monster_id', 'starts_at', 'ends_at']],
            'missing monster' => [['monster_id' => null], ['monster_id']],
            'unknown monster' => [['monster_id' => 999999], ['monster_id']],
            'non-integer monster' => [['monster_id' => 'invalid'], ['monster_id']],
            'missing start' => [['starts_at' => null], ['starts_at']],
            'missing end' => [['ends_at' => null], ['ends_at']],
            'start date only' => [['starts_at' => '2026-10-27'], ['starts_at']],
            'end date only' => [['ends_at' => '2026-10-27'], ['ends_at']],
            'malformed start' => [['starts_at' => 'not-a-date'], ['starts_at']],
            'malformed end' => [['ends_at' => 'not-a-date'], ['ends_at']],
            'impossible date' => [['starts_at' => '2026-02-30T10:15'], ['starts_at']],
            'impossible time' => [['ends_at' => '2026-10-27T25:45'], ['ends_at']],
            'space instead of T' => [['starts_at' => '2026-10-27 10:15'], ['starts_at']],
            'seconds not allowed' => [['starts_at' => '2026-10-27T10:15:00'], ['starts_at']],
            'UTC start not allowed' => [['starts_at' => '2026-10-27T03:15:00Z'], ['starts_at']],
            'UTC end not allowed' => [['ends_at' => '2026-10-27T05:45Z'], ['ends_at']],
            'explicit offset not allowed' => [['starts_at' => '2026-10-27T10:15+07:00'], ['starts_at']],
            'past by one minute' => [['starts_at' => '2026-10-26T07:59'], ['starts_at']],
            'equal endpoints' => [['ends_at' => '2026-10-27T10:15'], ['ends_at']],
            'reversed endpoints' => [['ends_at' => '2026-10-27T10:14'], ['ends_at']],
            '120 hours plus one minute' => [['ends_at' => '2026-11-01T10:16'], ['ends_at']],
        ];

        foreach (['store', 'availability'] as $endpoint) {
            foreach ($cases as $name => [$changes, $errors]) {
                yield $endpoint.' '.$name => [$endpoint, $changes, $errors];
            }
        }
    }

    public function test_wib_input_is_independent_of_a_clients_utc_timezone(): void
    {
        // At 01:00 UTC it is already 08:00 WIB: 07:59 is past, not future.
        $this->assertSame('2026-10-26 01:00', now()->utc()->format('Y-m-d H:i'));
        $this->actingAs($this->member)->withHeader('X-Timezone', 'UTC')
            ->postJson(route('loan.store'), $this->payload(['starts_at' => '2026-10-26T07:59']))
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->post(route('loan.store'), $this->payload([
            'starts_at' => '2026-10-26T08:01',
            'ends_at' => '2026-10-26T08:31',
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('2026-10-26 01:01', Loan::sole()->starts_at->utc()->format('Y-m-d H:i'));
    }

    public static function intervals(): array
    {
        // Existing reservation is [10:15, 12:45); all comparisons retain minutes.
        return [
            'identical' => ['2026-10-27T10:15', '2026-10-27T12:45', 0],
            'inside' => ['2026-10-27T11:00', '2026-10-27T12:00', 0],
            'contains existing' => ['2026-10-27T09:00', '2026-10-27T14:00', 0],
            'overlaps left' => ['2026-10-27T09:00', '2026-10-27T11:00', 0],
            'overlaps right' => ['2026-10-27T12:00', '2026-10-27T14:00', 0],
            'same start shorter' => ['2026-10-27T10:15', '2026-10-27T11:00', 0],
            'same start longer' => ['2026-10-27T10:15', '2026-10-27T14:00', 0],
            'same end shorter' => ['2026-10-27T11:00', '2026-10-27T12:45', 0],
            'same end longer' => ['2026-10-27T09:00', '2026-10-27T12:45', 0],
            'one minute overlap left' => ['2026-10-27T09:00', '2026-10-27T10:16', 0],
            'one minute overlap right' => ['2026-10-27T12:44', '2026-10-27T14:00', 0],
            'adjacent before' => ['2026-10-27T09:00', '2026-10-27T10:15', 1],
            'adjacent after' => ['2026-10-27T12:45', '2026-10-27T14:00', 1],
            'disjoint before' => ['2026-10-27T08:00', '2026-10-27T09:00', 1],
            'disjoint after' => ['2026-10-28T10:15', '2026-10-28T12:45', 1],
        ];
    }

    #[DataProvider('intervals')]
    public function test_one_unit_overlap_rules_and_store_recheck(string $start, string $end, int $quantity): void
    {
        $this->reservation();
        $this->monster->update(['status' => 'borrowed']);
        $this->assertTrue($this->monster->canSchedule());
        $this->assertSame($quantity, $this->available($start, $end));

        $response = $this->actingAs($this->member)->postJson(route('loan.store'), $this->payload([
            'starts_at' => $start, 'ends_at' => $end,
        ]));
        if ($quantity === 0) {
            $response->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        } else {
            $response->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('loans', 1 + $quantity);
        $this->assertDatabaseCount('loan_details', 1 + $quantity);
        $this->assertSame('borrowed', $this->monster->fresh()->status);
    }

    #[DataProvider('intervals')]
    public function test_availability_endpoint_returns_only_zero_or_one_without_writing(string $start, string $end, int $quantity): void
    {
        $this->reservation();
        $this->monster->update(['status' => 'borrowed']);
        $this->actingAs($this->member)
            ->getJson('/peminjaman/ketersediaan?'.http_build_query($this->payload([
                'starts_at' => $start, 'ends_at' => $end,
            ])))
            ->assertOk()->assertJsonPath('available_quantity', $quantity);

        $this->assertDatabaseCount('loans', 1);
        $this->assertDatabaseCount('loan_details', 1);
    }

    #[DataProvider('loanStatuses')]
    public function test_only_outstanding_statuses_block_the_unit(string $status, bool $blocks): void
    {
        // Keep the detail unreturned to prove the parent loan's status is checked.
        $this->reservation(status: $status);
        $this->assertSame($blocks ? 0 : 1, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));
        $response = $this->actingAs($this->member)->postJson(route('loan.store'), $this->payload());
        if ($blocks) {
            $response->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        } else {
            $response->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('loans', $blocks ? 1 : 2);
    }

    public static function loanStatuses(): array
    {
        return [
            'active' => ['active', true],
            'borrowed' => ['borrowed', true],
            'approved' => ['approved', true],
            'pending' => ['pending', true],
            'returned' => ['returned', false],
            'cancelled' => ['cancelled', false],
        ];
    }

    public function test_returned_detail_does_not_block_an_outstanding_parent_loan(): void
    {
        $this->reservation(returnedDetail: true);
        $this->assertSame(1, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));
        $this->actingAs($this->member)->post(route('loan.store'), $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('loans', 2);
    }

    #[DataProvider('unavailableUnits')]
    public function test_maintenance_and_manual_borrowed_units_are_unavailable(string $status, bool $withReservation): void
    {
        if ($withReservation) {
            $this->reservation('2026-10-28T10:15', '2026-10-28T12:45');
        }
        $this->monster->update(['status' => $status]);
        $this->assertFalse($this->monster->canSchedule());
        $this->assertSame(0, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));
        $this->actingAs($this->member)->postJson(route('loan.store'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseCount('loans', $withReservation ? 1 : 0);
        $this->assertSame($status, $this->monster->fresh()->status);
    }

    public static function unavailableUnits(): array
    {
        return [
            'manual borrowed without loans' => ['borrowed', false],
            'maintenance without loans' => ['maintenance', false],
            'maintenance even with disjoint reservation' => ['maintenance', true],
        ];
    }

    public function test_distinct_units_can_be_booked_for_the_same_time(): void
    {
        $this->reservation();
        $other = $this->makeMonster();
        $this->assertSame(0, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));
        $this->assertSame(1, $this->available('2026-10-27T10:15', '2026-10-27T12:45', $other));
        $this->actingAs($this->member)->post(route('loan.store'), $this->payload(['monster_id' => $other->id]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('borrowed', $other->fresh()->status);
        $this->assertDatabaseCount('loans', 2);
    }

    #[DataProvider('outstandingStatuses')]
    public function test_two_total_outstanding_loans_including_disjoint_future_reservations_block_a_third(string $status): void
    {
        $this->reservation('2026-12-01T10:00', '2026-12-01T11:00', $status, $this->member, $this->makeMonster());
        $this->reservation('2026-12-10T10:00', '2026-12-10T11:00', $status, $this->member, $this->makeMonster());
        $this->assertSame(1, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));

        $this->actingAs($this->member)->postJson(route('loan.store'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('monster_id');
        $this->assertDatabaseCount('loans', 2);
        $this->assertDatabaseCount('loan_details', 2);
        $this->assertSame('available', $this->monster->fresh()->status);
    }

    public static function outstandingStatuses(): array
    {
        return array_map(fn ($status) => [$status], ['active', 'borrowed', 'approved', 'pending']);
    }

    public function test_one_future_reservation_allows_a_second_and_terminal_loans_do_not_count(): void
    {
        $this->reservation('2026-12-01T10:00', '2026-12-01T11:00', 'pending', $this->member, $this->makeMonster());
        foreach (['returned', 'cancelled'] as $status) {
            $this->reservation(status: $status, user: $this->member, monster: $this->makeMonster());
        }
        $this->actingAs($this->member)->post(route('loan.store'), $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(2, $this->member->loans()->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])->count());
        $this->assertDatabaseCount('loans', 4);
    }

    public function test_a_successful_availability_check_is_not_a_booking_guarantee(): void
    {
        $url = '/peminjaman/ketersediaan?'.http_build_query($this->payload());
        $this->actingAs($this->member)->getJson($url)->assertOk()->assertJsonPath('available_quantity', 1);
        $this->assertDatabaseCount('loans', 0);

        // Sequential stale-read regression, not a concurrency/locking test.
        $this->reservation();
        $this->monster->update(['status' => 'borrowed']);
        $this->postJson(route('loan.store'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseCount('loans', 1);
    }

    public function test_returning_one_reservation_keeps_the_unit_borrowed_until_the_last_is_returned(): void
    {
        $first = $this->reservation();
        $second = $this->reservation('2026-10-28T10:15', '2026-10-28T12:45', 'pending');
        $this->monster->update(['status' => 'borrowed']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.loans.process-return', $first), ['confirm_physical' => '1'])
            ->assertRedirect(route('admin.loans.index'))->assertSessionHasNoErrors();

        $this->assertSame('returned', $first->fresh()->status);
        $this->assertNotNull($first->details()->sole()->returned_at);
        $this->assertSame('borrowed', $this->monster->fresh()->status);
        $this->assertSame(0, $this->available('2026-10-28T10:15', '2026-10-28T12:45'));
        $this->assertSame(1, $this->available('2026-10-27T10:15', '2026-10-27T12:45'));

        $this->post(route('admin.loans.process-return', $second), ['confirm_physical' => '1'])
            ->assertRedirect(route('admin.loans.index'))->assertSessionHasNoErrors();
        $this->assertSame('available', $this->monster->fresh()->status);
    }

    public function test_raw_legacy_loan_uses_immutable_midnight_fallback_and_inclusive_due_date(): void
    {
        $id = DB::table('loans')->insertGetId([
            'loan_code' => 'LEGACY-'.Str::ulid(),
            'user_id' => $this->member->id,
            'loan_date' => '2026-10-27',
            'due_date' => '2026-10-28',
            'status' => 'active',
        ]);
        DB::table('loan_details')->insert(['loan_id' => $id, 'monster_id' => $this->monster->id]);
        $loan = Loan::findOrFail($id);
        $this->assertNull($loan->starts_at);
        $this->assertNull($loan->ends_at);
        $this->assertInstanceOf(CarbonImmutable::class, $loan->scheduled_start);
        $this->assertInstanceOf(CarbonImmutable::class, $loan->scheduled_end);
        $this->assertSame('2026-10-27 00:00:00 +07:00', $loan->scheduled_start->format('Y-m-d H:i:s P'));
        $this->assertSame('2026-10-29 00:00:00 +07:00', $loan->scheduled_end->format('Y-m-d H:i:s P'));
        $this->assertSame(0, $this->available('2026-10-28T23:59', '2026-10-29T00:01'));
        $this->assertSame(1, $this->available('2026-10-26T23:00', '2026-10-27T00:00'));
        $this->assertSame(1, $this->available('2026-10-29T00:00', '2026-10-29T01:00'));
        $this->assertSame('2026-10-28', $loan->due_date->toDateString());
        $this->assertNull(DB::table('loans')->where('id', $id)->value('ends_at'));
    }

    public function test_form_requires_editable_schedule_inputs_without_automatic_dates(): void
    {
        $response = $this->actingAs($this->member)->get(route('unit.detail', ['id' => $this->monster->id]));
        $response->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $forms = $xpath->query('//form[@action="'.route('loan.store').'"]');
        $this->assertSame(1, $forms->length);
        foreach (['starts_at', 'ends_at'] as $name) {
            $inputs = $xpath->query('.//input[@name="'.$name.'"]', $forms->item(0));
            $this->assertSame(1, $inputs->length, 'Missing '.$name.' inside the loan form.');
            $input = $inputs->item(0);
            $this->assertSame('datetime-local', $input->getAttribute('type'));
            $this->assertTrue($input->hasAttribute('required'));
            $this->assertFalse($input->hasAttribute('readonly'));
            $this->assertFalse($input->hasAttribute('disabled'));
            $this->assertSame('', $input->getAttribute('value'), 'Do not silently assign a schedule.');
        }
        $response->assertDontSee('Tanggal otomatis dan tidak dapat diubah.');
    }

    #[DataProvider('schedulePages')]
    public function test_schedule_pages_display_both_times_and_wib(string $route, string $role, string $status, string $format): void
    {
        $loan = $this->reservation(status: $status, user: $this->member, returnedDetail: $status === 'returned');
        $parameters = match ($route) {
            'loan.success' => ['code' => $loan->loan_code],
            'admin.loans.return' => ['loan' => $loan->id],
            default => [],
        };
        $viewer = $role === 'member' ? $this->member : User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($viewer)->get(route($route, $parameters));
        $response->assertOk();
        foreach ([$loan->starts_at, $loan->ends_at] as $time) {
            $response->assertSee($time->locale('id')->translatedFormat($format).' WIB');
        }
    }

    public static function schedulePages(): array
    {
        return [
            'member confirmation' => ['loan.success', 'member', 'active', 'd F Y H:i'],
            'member active' => ['loan.active', 'member', 'active', 'd F Y H:i'],
            'member history' => ['loan.history', 'member', 'returned', 'd F Y H:i'],
            'member print' => ['loan.history.print', 'member', 'returned', 'd M Y H:i'],
            'admin active' => ['admin.loans.index', 'admin', 'active', 'd M Y H:i'],
            'admin return' => ['admin.loans.return', 'admin', 'active', 'd F Y H:i'],
            'admin history' => ['admin.loans.history', 'admin', 'returned', 'd M Y H:i'],
            'admin print' => ['admin.loans.print', 'admin', 'returned', 'd M Y H:i'],
        ];
    }
}
