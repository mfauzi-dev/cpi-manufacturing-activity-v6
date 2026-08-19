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
        Schema::create('ps_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cost_center_id')
                ->constrained('cost_centers')
                ->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_groups');
    }
};
