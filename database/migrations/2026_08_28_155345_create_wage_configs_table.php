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
        Schema::create('wage_configs', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();
            $table->decimal('ump', 15, 2);
            $table->unsignedTinyInteger('hari_kerja_standar')->default(25);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wage_configs');
    }
};
