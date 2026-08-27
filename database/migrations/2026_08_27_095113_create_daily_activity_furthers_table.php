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
        Schema::create('daily_activity_furthers', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
 
            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();
 
            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->cascadeOnDelete();
 
            $table->foreignId('ps_group_id')
                ->constrained('ps_groups')
                ->cascadeOnDelete();
 
            $table->foreignId('line_id')
                ->constrained('lines')
                ->cascadeOnDelete();
 
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
 
            $table->foreignId('input_by')
                ->constrained('users')
                ->cascadeOnDelete();
 
            $table->timestamps();
 
            $table->index(['tanggal', 'cost_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_furthers');
    }
};
