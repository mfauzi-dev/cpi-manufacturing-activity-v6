<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_activity_slaughter_houses', function (Blueprint $table) {
            DB::statement('ALTER TABLE daily_activity_slaughter_houses MODIFY product_group_id BIGINT UNSIGNED NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_slaughter_houses', function (Blueprint $table) {
            DB::statement('ALTER TABLE daily_activity_slaughter_houses MODIFY product_group_id BIGINT UNSIGNED NOT NULL');
        });
    }
};
