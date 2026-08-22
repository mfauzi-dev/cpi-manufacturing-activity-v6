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
        Schema::create('daily_production_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_production_id')->constrained('daily_productions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('total_kg', 12, 2);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('total_harga', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_production_details');
    }
};
