<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->index(['status', 'starts_at', 'ends_at']);
        });

        DB::table('loans')->orderBy('id')->chunkById(200, function ($loans) {
            foreach ($loans as $loan) {
                // Date-only loans retain the entire original due date in WIB.
                DB::table('loans')->where('id', $loan->id)->update([
                    'starts_at' => CarbonImmutable::parse($loan->loan_date, 'Asia/Jakarta')->startOfDay()->format('Y-m-d H:i:s'),
                    'ends_at' => CarbonImmutable::parse($loan->due_date, 'Asia/Jakarta')->addDay()->startOfDay()->format('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['status', 'starts_at', 'ends_at']);
            $table->dropColumn(['starts_at', 'ends_at']);
        });
    }
};
