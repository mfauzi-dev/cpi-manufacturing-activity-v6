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
        Schema::create('penggajian_borongans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('period_month');

            $table->year('period_year');

            $table->decimal('total_kg', 15, 2)->default(0);

            $table->decimal('total_upah', 15, 2)->default(0);

            $table->timestamps();

            $table->unique([
                'employee_id',
                'period_month',
                'period_year'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian_borongans');
    }
};
