<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('fines')->update([
            'amount' => DB::raw('amount * 1000'),
        ]);
    }

    public function down(): void
    {
        DB::table('fines')->update([
            'amount' => DB::raw('amount / 1000'),
        ]);
    }
};
