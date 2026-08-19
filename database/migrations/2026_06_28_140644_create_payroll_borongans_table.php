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
        Schema::create('payroll_borongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            $table->integer('period_month');
            $table->integer('period_year');

            // hasil rekap dari daily earnings
            $table->integer('work_days')->default(0);
            $table->decimal('total_kg')->default(0);

            $table->decimal('total_earning', 15, 2)->default(0);

            // potongan
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0);
            $table->decimal('jaminan_pensiun', 15, 2)->default(0);
            $table->decimal('jht', 15, 2)->default(0);
            $table->decimal('management_fee', 15, 2)->default(0);

            // final
            $table->decimal('net_salary', 15, 2)->default(0);

            $table->enum('status', ['DRAFT', 'FINAL'])->default('DRAFT');

            $table->foreignId('generated_by')->nullable()->constrained('users');
            $table->timestamp('generated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_borongans');
    }
};
