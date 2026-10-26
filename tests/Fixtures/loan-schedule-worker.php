<?php

use App\Http\Controllers\Member\LoanController;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';

try {
    [$script, $directory, $worker, $userId, $monsterId, $frozenNow, $start, $end] = $argv;
    $database = realpath($directory.'/database.sqlite');
    if ($database === false || dirname($database) !== realpath($directory)
        || ! str_starts_with(basename($directory), 'loan-schedule-concurrency-')) {
        throw new RuntimeException('Worker requires an existing, isolated temporary SQLite database.');
    }

    $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    if (config('database.default') !== 'sqlite'
        || config('database.connections.sqlite.database') !== ':memory:'
        || ! empty(config('database.connections.sqlite.url'))) {
        throw new RuntimeException('Worker bootstrap must retain the default in-memory database guard.');
    }

    config(['database.connections.loan_schedule_concurrency' => [
        'driver' => 'sqlite',
        'url' => null,
        'database' => $database,
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => 10000,
        'transaction_mode' => 'DEFERRED',
    ]]);
    DB::setDefaultConnection('loan_schedule_concurrency');

    Carbon::setTestNow(CarbonImmutable::parse($frozenNow));
    CarbonImmutable::setTestNow(CarbonImmutable::parse($frozenNow));
    auth()->login(User::findOrFail((int) $userId));
    $request = Request::create('/peminjaman', 'POST', [
        'monster_id' => (int) $monsterId,
        'starts_at' => $start,
        'ends_at' => $end,
    ]);
    $request->setUserResolver(fn () => auth()->user());

    $identity = [
        'pid' => getmypid(),
        'database' => DB::selectOne('PRAGMA database_list')->file,
        'busy_timeout' => (int) DB::selectOne('PRAGMA busy_timeout')->timeout,
    ];
    $readyPath = $directory.'/ready-'.$worker;
    if (file_put_contents($readyPath.'.tmp', json_encode($identity, JSON_THROW_ON_ERROR)) === false
        || ! rename($readyPath.'.tmp', $readyPath)) {
        throw new RuntimeException('Cannot signal worker readiness.');
    }

    // No availability check is performed here: both real saves race after release.
    $deadline = microtime(true) + 15;
    while (! is_file($directory.'/release')) {
        if (microtime(true) >= $deadline) {
            throw new RuntimeException('Timed out waiting for the shared release barrier.');
        }
        usleep(1000);
    }

    try {
        $response = $app->make(LoanController::class)->store($request);
        $result = ['outcome' => 'success', 'status' => $response->getStatusCode(), 'location' => $response->getTargetUrl()];
    } catch (ValidationException $exception) {
        $result = ['outcome' => 'validation', 'exception' => $exception::class, 'errors' => $exception->errors()];
    }

    echo json_encode($identity + $result, JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage().PHP_EOL.$exception->getTraceAsString().PHP_EOL);
    exit(1);
} finally {
    if (isset($app)) {
        DB::disconnect('loan_schedule_concurrency');
    }
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
}
