<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_detail_id')
                ->unique()
                ->constrained('loan_details')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('overdue_days')->default(0);
            $table->string('status')->default('unpaid');
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};