<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_hours_actual', 5, 2);
            $table->decimal('total_hours_konversi', 5, 2)->nullable();
            $table->decimal('hourly_rate', 15, 2);
            $table->decimal('overtime_amount', 15, 2);

            $table->enum('status', [
                'PENDING',
                'APPROVED',
                'REJECTED',
            ])->default('pending');

            $table->text('description')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};