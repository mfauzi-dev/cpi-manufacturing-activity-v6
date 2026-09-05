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
            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity', 'productivity_tmp');
            });

            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity_actual', 'productivity');
            });

            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity_tmp', 'productivity_actual');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity', 'productivity_tmp');
            });

            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity_actual', 'productivity');
            });

            Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
                $table->renameColumn('productivity_tmp', 'productivity_actual');
            });
        });
    }
};
