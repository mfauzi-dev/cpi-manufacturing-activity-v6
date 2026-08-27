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
        Schema::create('daily_activity_detail_furthers', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('daily_activity_further_id')
                ->constrained('daily_activity_furthers')
                ->cascadeOnDelete();
 
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
 
            $table->decimal('total_kg', 12, 2)->default(0);
 
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
        Schema::dropIfExists('daily_activity_detail_furthers');
    }
};
