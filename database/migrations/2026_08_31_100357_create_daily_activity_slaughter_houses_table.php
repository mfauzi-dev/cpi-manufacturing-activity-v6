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
        Schema::create('daily_activity_slaughter_houses', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('cost_center_id')->constrained('cost_centers');
            $table->foreignId('ps_group_id')->constrained('ps_groups');
            $table->foreignId('product_group_id')->constrained('product_groups');
            $table->foreignId('line_id')->constrained('lines');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('input_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_slaughter_houses');
    }
};
