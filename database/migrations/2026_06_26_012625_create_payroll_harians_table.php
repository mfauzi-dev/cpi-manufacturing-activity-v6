<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->integer('standard_days');
            $table->integer('work_days');
            $table->decimal('basic_salary', 15, 2); 
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0);
            $table->decimal('jaminan_pensiun', 15, 2)->default(0);
            $table->decimal('jht', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->enum('status', ['DRAFT', 'FINAL'])->default('DRAFT');

            $table->timestamps();
            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_harians');
    }
};
