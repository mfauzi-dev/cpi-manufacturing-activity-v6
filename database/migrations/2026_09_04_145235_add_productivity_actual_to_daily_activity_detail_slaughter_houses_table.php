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
            $table->decimal('productivity_actual', 10, 2)->nullable()->after('productivity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_detail_slaughter_houses', function (Blueprint $table) {
            $table->dropColumn('productivity_actual');
        });
    }
};
