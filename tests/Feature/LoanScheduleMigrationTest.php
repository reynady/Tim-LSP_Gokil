<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoanScheduleMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        // The down/up exercise below must never touch a persistent database.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
    }

    public function test_schedule_migration_backfills_raw_legacy_rows_without_changing_original_dates(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $migration = require database_path('migrations/2026_10_26_000000_add_schedule_to_loans_table.php');
        $migration->down();

        $expected = [];
        try {
            $this->assertFalse(Schema::hasColumn('loans', 'starts_at'));
            $this->assertFalse(Schema::hasColumn('loans', 'ends_at'));

            // Cross a chunk boundary, a year boundary, and leap-day/month ends.
            $dates = [
                ['2026-12-31', '2027-01-01'],
                ['2024-02-28', '2024-02-29'],
                ['2026-10-31', '2026-10-31'],
            ];
            for ($i = 0; $i < 203; $i++) {
                [$start, $due] = $dates[$i % count($dates)];
                $row = [
                    'loan_code' => 'LEGACY-MIGRATION-'.$i,
                    'user_id' => $user->id,
                    'loan_date' => $start,
                    'due_date' => $due,
                    'status' => ['active', 'pending', 'returned', 'cancelled'][$i % 4],
                    'notes' => 'Preserve legacy notes '.$i,
                    'created_at' => '2026-01-01 01:02:03',
                    'updated_at' => '2026-01-02 04:05:06',
                ];
                $id = DB::table('loans')->insertGetId($row);
                $expected[$id] = $row;
            }
        } finally {
            // Restore the schema even when fixture creation/assertions fail.
            $migration->up();
        }

        $this->assertTrue(Schema::hasColumns('loans', ['starts_at', 'ends_at']));
        $this->assertDatabaseCount('loans', 203);
        foreach ($expected as $id => $legacy) {
            $row = (array) DB::table('loans')->find($id);
            foreach ($legacy as $column => $value) {
                $this->assertSame($value, $row[$column], 'Migration changed legacy '.$column.' for loan '.$id);
            }
            $this->assertSame($legacy['loan_date'].' 00:00:00', $row['starts_at']);
            $end = CarbonImmutable::parse($legacy['due_date'], 'Asia/Jakarta')->addDay()->startOfDay();
            $this->assertSame($end->format('Y-m-d H:i:s'), $row['ends_at']);
        }

        $loan = Loan::findOrFail(array_key_first($expected));
        $this->assertInstanceOf(CarbonImmutable::class, $loan->starts_at);
        $this->assertInstanceOf(CarbonImmutable::class, $loan->ends_at);
        $this->assertSame('2026-12-31 00:00:00 +07:00', $loan->scheduled_start->format('Y-m-d H:i:s P'));
        $this->assertSame('2027-01-02 00:00:00 +07:00', $loan->scheduled_end->format('Y-m-d H:i:s P'));
    }

    public function test_schedule_columns_remain_nullable_for_raw_legacy_imports(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $id = DB::table('loans')->insertGetId([
            'loan_code' => 'NULLABLE-LEGACY',
            'user_id' => $user->id,
            'loan_date' => '2026-10-27',
            'due_date' => '2026-10-28',
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $row = DB::table('loans')->find($id);
        $this->assertNull($row->starts_at);
        $this->assertNull($row->ends_at);
        $this->assertSame('2026-10-27', $row->loan_date);
        $this->assertSame('2026-10-28', $row->due_date);
    }
}
