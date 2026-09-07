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
        Schema::table('penggajian_harians', function (Blueprint $table) {
            $table->decimal('overtime_total', 15, 2)->default(0)->after('upah_harian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajian_harians', function (Blueprint $table) {
            $table->dropColumn('overtime_total');
        });
    }
};
