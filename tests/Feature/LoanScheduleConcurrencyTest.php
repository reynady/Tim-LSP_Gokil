<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Monster;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LoanScheduleConcurrencyTest extends TestCase
{
    private const CONNECTION = 'loan_schedule_concurrency';

    private const NOW = '2030-01-10T08:00:00+07:00';

    private const START = '2030-01-11T10:15';

    private const END = '2030-01-11T12:45';

    private ?string $directory = null;

    /** @var list<Process> */
    private array $workers = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Never migrate or replace the default connection used by the memory tests.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->travelTo(CarbonImmutable::parse(self::NOW));

        $directory = sys_get_temp_dir().'/loan-schedule-concurrency-'.bin2hex(random_bytes(12));
        $this->assertTrue(mkdir($directory, 0700));
        $this->directory = realpath($directory);
        $this->assertTrue(touch($this->directory.'/database.sqlite'));

        config(['database.connections.'.self::CONNECTION => [
            'driver' => 'sqlite',
            'url' => null,
            'database' => $this->directory.'/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
            'busy_timeout' => 10000,
            'transaction_mode' => 'DEFERRED',
        ]]);
        $exitCode = Artisan::call('migrate', ['--database' => self::CONNECTION, '--force' => true]);
        $this->assertSame(0, $exitCode, Artisan::output());
        $this->assertSame('sqlite', DB::getDefaultConnection());
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(1);
                }
            }
        } finally {
            try {
                DB::purge(self::CONNECTION);
                if ($this->directory !== null) {
                    File::deleteDirectory($this->directory);
                }
            } finally {
                parent::tearDown();
            }
        }
    }

    public function test_two_members_cannot_save_overlapping_schedules_for_the_same_unit(): void
    {
        $members = [$this->member(), $this->member()];
        $monster = $this->monster('SHARED');

        $results = $this->race([
            [$members[0]->id, $monster->id],
            [$members[1]->id, $monster->id],
        ]);
        $winner = $this->assertOneSuccessAndOneValidation($results, [
            'starts_at' => ['Produk tidak tersedia pada jadwal yang dipilih. Pilih jadwal lain atau produk lain.'],
        ]);

        $this->assertSame(1, Loan::on(self::CONNECTION)->count());
        $this->assertSame(1, LoanDetail::on(self::CONNECTION)->count());
        $loan = Loan::on(self::CONNECTION)->with('details')->sole();
        $this->assertSavedSchedule($loan, $members[$winner]->id, $monster->id, $results[$winner]);
        $this->assertSame(0, Loan::on(self::CONNECTION)->where('user_id', $members[1 - $winner]->id)->count());
        $this->assertSame('borrowed', $monster->fresh()->status);
        $this->assertSame(0, $monster->fresh()->availableUnitsDuring(
            CarbonImmutable::parse(self::START, config('app.timezone')),
            CarbonImmutable::parse(self::END, config('app.timezone')),
        ));
    }

    public function test_one_member_cannot_exceed_two_active_loans_by_saving_different_units_concurrently(): void
    {
        $member = $this->member();
        $existingMonster = $this->monster('EXISTING');
        $existingLoan = Loan::on(self::CONNECTION)->create([
            'loan_code' => 'EXISTING-RESERVATION',
            'user_id' => $member->id,
            'loan_date' => '2030-01-12',
            'due_date' => '2030-01-12',
            'starts_at' => '2030-01-12 09:00:00',
            'ends_at' => '2030-01-12 10:00:00',
            'status' => 'active',
        ]);
        $existingLoan->details()->create(['monster_id' => $existingMonster->id]);
        $existingMonster->update(['status' => 'borrowed']);
        $monsters = [$this->monster('FIRST'), $this->monster('SECOND')];

        $results = $this->race([
            [$member->id, $monsters[0]->id],
            [$member->id, $monsters[1]->id],
        ]);
        $winner = $this->assertOneSuccessAndOneValidation($results, [
            'monster_id' => ['Peminjaman ditolak: batas maksimal 2 peminjaman aktif, termasuk reservasi mendatang, telah tercapai.'],
        ]);

        $this->assertSame(2, Loan::on(self::CONNECTION)->count());
        $this->assertSame(2, LoanDetail::on(self::CONNECTION)->count());
        $this->assertSame(2, Loan::on(self::CONNECTION)->where('user_id', $member->id)
            ->whereIn('status', Loan::STOCK_LOCKING_STATUSES)->count());
        $loan = Loan::on(self::CONNECTION)->with('details')->whereKeyNot($existingLoan->id)->sole();
        $this->assertSavedSchedule($loan, $member->id, $monsters[$winner]->id, $results[$winner]);
        $this->assertSame('active', $existingLoan->fresh()->status);
        $this->assertSame('borrowed', $monsters[$winner]->fresh()->status);
        $this->assertSame('available', $monsters[1 - $winner]->fresh()->status);
        $this->assertSame(0, LoanDetail::on(self::CONNECTION)->where('monster_id', $monsters[1 - $winner]->id)->count());
    }

    private function member(): User
    {
        $member = User::factory()->make(['role' => 'member', 'is_active' => true]);
        $member->setConnection(self::CONNECTION)->save();

        return $member;
    }

    private function monster(string $code): Monster
    {
        return Monster::on(self::CONNECTION)->create([
            'monster_code' => $code,
            'name' => 'Concurrency test unit',
            'status' => 'available',
        ]);
    }

    /** @param list<array{int, int}> $bookings */
    private function race(array $bookings): array
    {
        foreach ($bookings as $index => [$userId, $monsterId]) {
            $worker = new Process([
                PHP_BINARY, base_path('tests/Fixtures/loan-schedule-worker.php'),
                $this->directory, (string) $index, (string) $userId, (string) $monsterId,
                self::NOW, self::START, self::END,
            ], base_path(), [
                'APP_ENV' => 'testing',
                'APP_CONFIG_CACHE' => $this->directory.'/nonexistent-config.php',
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => ':memory:',
                'DB_URL' => '',
                'SESSION_DRIVER' => 'array',
                'CACHE_STORE' => 'array',
                'QUEUE_CONNECTION' => 'sync',
                'LOG_CHANNEL' => 'stderr',
            ]);
            $worker->setTimeout(25);
            $this->workers[] = $worker;
            $worker->start();
        }

        $deadline = microtime(true) + 10;
        do {
            $ready = true;
            foreach ($this->workers as $index => $worker) {
                $worker->checkTimeout();
                $this->assertTrue($worker->isRunning(), 'Worker exited before release: '.$worker->getOutput().$worker->getErrorOutput());
                $ready = $ready && is_file($this->directory.'/ready-'.$index);
            }
            if ($ready) {
                break;
            }
            usleep(1000);
        } while (microtime(true) < $deadline);
        $this->assertTrue($ready, 'Both workers must reach the barrier before either save is released.');

        $identities = [];
        foreach ($this->workers as $index => $worker) {
            $identity = json_decode(file_get_contents($this->directory.'/ready-'.$index), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($this->directory.'/database.sqlite', $identity['database']);
            $this->assertGreaterThanOrEqual(5000, $identity['busy_timeout']);
            $identities[] = $identity;
        }
        $this->assertNotSame($identities[0]['pid'], $identities[1]['pid']);
        $this->assertNotSame(getmypid(), $identities[0]['pid']);
        $this->assertNotSame(getmypid(), $identities[1]['pid']);
        $this->assertSame(1, file_put_contents($this->directory.'/release', '1'));

        $results = [];
        foreach ($this->workers as $index => $worker) {
            $exitCode = $worker->wait();
            $this->assertSame(0, $exitCode, $worker->getOutput().$worker->getErrorOutput());
            $result = json_decode(trim($worker->getOutput()), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($identities[$index], array_intersect_key($result, $identities[$index]));
            $results[] = $result;
        }

        return $results;
    }

    private function assertOneSuccessAndOneValidation(array $results, array $errors): int
    {
        $outcomes = array_column($results, 'outcome');
        sort($outcomes);
        $this->assertSame(['success', 'validation'], $outcomes);
        $winner = $results[0]['outcome'] === 'success' ? 0 : 1;
        $this->assertSame(302, $results[$winner]['status']);
        $this->assertSame(ValidationException::class, $results[1 - $winner]['exception']);
        $this->assertSame($errors, $results[1 - $winner]['errors']);

        return $winner;
    }

    private function assertSavedSchedule(Loan $loan, int $userId, int $monsterId, array $result): void
    {
        $this->assertSame($userId, $loan->user_id);
        $this->assertSame('active', $loan->status);
        $this->assertSame('2030-01-11 10:15:00', $loan->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2030-01-11 12:45:00', $loan->ends_at->format('Y-m-d H:i:s'));
        $this->assertSame('2030-01-11', $loan->loan_date->toDateString());
        $this->assertSame('2030-01-11', $loan->due_date->toDateString());
        $this->assertCount(1, $loan->details);
        $this->assertSame($monsterId, $loan->details->sole()->monster_id);
        $this->assertNull($loan->details->sole()->returned_at);
        $this->assertSame(route('loan.success', ['code' => $loan->loan_code]), $result['location']);
    }
}
