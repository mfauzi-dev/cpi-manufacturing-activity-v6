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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
            $table->foreignId('product_group_id')->nullable()->constrained('product_groups')->cascadeOnDelete();
            $table->string('material_code')->nullable();
            $table->string('material_name');
            $table->decimal('harga_per_kg')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
