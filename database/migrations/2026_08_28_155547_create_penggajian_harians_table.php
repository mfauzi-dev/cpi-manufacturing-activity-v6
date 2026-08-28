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
        Schema::create('penggajian_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->year('period_year');

            $table->unsignedInteger('work_days')->default(0);

            $table->decimal('ump_used', 15, 2)->default(0);
            $table->unsignedTinyInteger('hari_kerja_standar_used')->default(25);

            $table->decimal('upah_harian', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['employee_id', 'period_month', 'period_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian_harians');
    }
};
