<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penggajian_karyawan_tetaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->year('period_year');
            $table->decimal('basic_salary', 15, 2);

            $table->decimal('overtime_total', 15, 2)->default(0);
            $table->decimal('grand_total_salary', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajian_karyawan_tetaps');
    }
};