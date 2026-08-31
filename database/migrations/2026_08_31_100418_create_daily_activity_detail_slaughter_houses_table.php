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
        Schema::create('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_activity_slaughter_house_id'); 
            $table->foreign('daily_activity_slaughter_house_id', 'detail_slaughter_house_activity_fk')
                  ->references('id')
                  ->on('daily_activity_slaughter_houses')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('total_kg', 12, 2);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('total_harga', 15, 2); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_detail_slaughter_houses');
    }
};
