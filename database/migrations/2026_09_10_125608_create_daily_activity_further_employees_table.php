<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_activity_further_employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_activity_further_id')
                ->constrained('daily_activity_furthers', indexName: 'daf_employees_daf_id_foreign')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees', indexName: 'daf_employees_employee_id_foreign')
                ->restrictOnDelete();

            $table->decimal('jumlah_hk', 8, 2);

            $table->timestamps();

            $table->unique(
                ['daily_activity_further_id', 'employee_id'],
                'daf_employees_daf_id_employee_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activity_further_employees');
    }
};