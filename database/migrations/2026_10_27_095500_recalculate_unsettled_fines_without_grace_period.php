<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('fines')
            ->where('overdue_days', '>', 0)
            ->whereIn('status', ['unpaid', 'waived'])
            ->update([
                'amount' => DB::raw('overdue_days * 25'),
                'status' => 'unpaid',
            ]);
    }

    public function down(): void
    {
        DB::table('fines')
            ->where('overdue_days', '>', 0)
            ->where('status', 'unpaid')
            ->update([
                'amount' => DB::raw('CASE WHEN overdue_days > 5 THEN (overdue_days - 5) * 25 ELSE 0 END'),
                'status' => DB::raw("CASE WHEN overdue_days > 5 THEN 'unpaid' ELSE 'waived' END"),
            ]);
    }
};
