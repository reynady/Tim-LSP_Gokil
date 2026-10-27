<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('max_active_loans')->default(2);
            $table->unsignedTinyInteger('max_duration_days')->default(5);
            $table->timestamps();
        });

        DB::table('loan_policies')->insert([
            'id' => 1,
            'max_active_loans' => 2,
            'max_duration_days' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_policies');
    }
};
