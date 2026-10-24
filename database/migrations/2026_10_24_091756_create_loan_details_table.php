<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('monster_id')
                ->constrained('monsters')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime('returned_at')->nullable();

            $table->timestamps();

            $table->index(['loan_id', 'monster_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_details');
    }
};