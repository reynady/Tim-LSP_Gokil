<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DAILY_RATE_RUPIAH = 25_000;

    public function up(): void
    {
        DB::table('fines')->update([
            'amount' => DB::raw('overdue_days * '.self::DAILY_RATE_RUPIAH),
        ]);
    }

    public function down(): void
    {
        DB::table('fines')->update([
            'amount' => DB::raw('overdue_days * 25'),
        ]);
    }
};
