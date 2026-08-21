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
        Schema::create('daily_activity_details', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('daily_activity_id')
                ->constrained('daily_activities')
                ->cascadeOnDelete();
 
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
 
            $table->decimal('total_kg', 12, 2)->default(0);
 
            $table->decimal('harga_per_kg', 14, 2)->default(0);
 
            $table->decimal('total_harga', 16, 2)->default(0);

            $table->decimal('lama_packing', 16, 2)->default(0);

            $table->decimal('productivity', 16, 2)->default(0);
 
            $table->timestamps();
 
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_details');
    }
};
