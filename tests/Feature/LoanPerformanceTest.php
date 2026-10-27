<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Monster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_fines_page_handles_two_hundred_loans_without_n_plus_one_queries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $monster = Monster::create([
            'monster_code' => 'PERF-UNIT',
            'name' => 'Performance test unit',
            'status' => 'available',
        ]);
        $dueDate = now()->subDays(8);
        $returnedAt = now()->subDays(6);

        for ($index = 1; $index <= 200; $index++) {
            $isReturned = $index > 100;
            $loan = Loan::create([
                'loan_code' => sprintf('PERF-%03d', $index),
                'user_id' => $member->id,
                'returned_by' => $isReturned ? $admin->id : null,
                'loan_date' => $dueDate->copy()->subDays(5),
                'due_date' => $dueDate,
                'status' => $isReturned ? 'returned' : 'borrowed',
            ]);
            $loan->details()->create([
                'monster_id' => $monster->id,
                'returned_at' => $isReturned ? $returnedAt : null,
            ]);
        }

        $durationsMs = [];
        $maxQueriesPerRequest = 0;
        $this->actingAs($admin);
        DB::enableQueryLog();

        try {
            for ($request = 0; $request < 5; $request++) {
                DB::flushQueryLog();
                $startedAt = hrtime(true);
                $response = $this->get(route('admin.fines.index'));
                $durationsMs[] = (hrtime(true) - $startedAt) / 1_000_000;
                $maxQueriesPerRequest = max($maxQueriesPerRequest, count(DB::getQueryLog()));
                $response->assertOk();
                $response->assertSee('PERF-001');
                $response->assertSee('PERF-200');
            }
        } finally {
            DB::disableQueryLog();
        }

        sort($durationsMs);
        $medianMs = $durationsMs[2];
        $this->assertLessThanOrEqual(20, $maxQueriesPerRequest, 'Eager loading should keep query count independent of loan count.');
        fwrite(STDERR, sprintf(
            "Benchmark: 200 loans, 5 requests, median %.1f ms/request, max %d queries/request\n",
            $medianMs,
            $maxQueriesPerRequest,
        ));
    }
}
