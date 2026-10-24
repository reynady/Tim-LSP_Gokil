<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();

            $table->string('monster_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('condition')->nullable();
            $table->string('image')->nullable();

            $table->string('threat_level')->default('Unknown');
            $table->string('origin_type')->default('Anomaly');

            $table->string('status')->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};