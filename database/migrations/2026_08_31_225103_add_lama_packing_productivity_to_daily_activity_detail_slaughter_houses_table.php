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
        Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
            $table->decimal('lama_packing', 16, 2)
                ->default(0)
                ->after('total_kg');

            $table->decimal('productivity', 16, 2)
                ->default(0)
                ->after('lama_packing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
            $table->dropColumn([
                'lama_packing',
                'productivity',
            ]);
        });
    }
};
