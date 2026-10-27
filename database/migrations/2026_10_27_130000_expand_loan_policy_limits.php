<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_policies', function (Blueprint $table) {
            $table->unsignedBigInteger('max_active_loans')->default(2)->change();
            $table->unsignedBigInteger('max_duration_days')->default(5)->change();
        });
    }

    public function down(): void
    {
        $hasValuesOutsideTinyInteger = DB::table('loan_policies')
            ->where('max_active_loans', '>', 255)
            ->orWhere('max_duration_days', '>', 255)
            ->exists();

        if ($hasValuesOutsideTinyInteger) {
            throw new RuntimeException(
                'Cannot restore tiny integer loan policy columns while a policy value exceeds 255.',
            );
        }

        Schema::table('loan_policies', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_active_loans')->default(2)->change();
            $table->unsignedTinyInteger('max_duration_days')->default(5)->change();
        });
    }
};
