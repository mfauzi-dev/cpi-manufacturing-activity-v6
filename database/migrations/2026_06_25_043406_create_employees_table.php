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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outsourcing_id')
                ->nullable()
                ->constrained('outsourcings')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('cost_center_id')
                ->nullable()
                ->constrained('cost_centers')
                ->nullOnDelete();

            $table->foreignId('ps_group_id')
                ->nullable()
                ->constrained('ps_groups')
                ->nullOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();

            $table->string('nik')->nullable();
            $table->string('name');
            $table->enum('employment_status', ['permanent', 'outsourcing']);
 
            $table->enum('employee_status', ['cpi', 'borongan', 'harian'])->nullable();
            $table->string('personel_area')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
