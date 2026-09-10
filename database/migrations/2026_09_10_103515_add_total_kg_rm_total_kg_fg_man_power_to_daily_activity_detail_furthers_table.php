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
        Schema::table('daily_activity_detail_furthers', function (Blueprint $table) {
            $table->renameColumn('total_kg', 'total_kg_rm');
            $table->renameColumn('lama_packing', 'man_power');
        });

        Schema::table('daily_activity_detail_furthers', function (Blueprint $table) {
            $table->decimal('total_kg_fg', 15, 2)->default(0)->after('total_kg_rm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_detail_furthers', function (Blueprint $table) {
            $table->dropColumn('total_kg_fg');
        });

        Schema::table('daily_activity_detail_furthers', function (Blueprint $table) {
            $table->renameColumn('total_kg_rm', 'total_kg');
            $table->renameColumn('man_power', 'lama_packing');
        });
    }
};
