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
            $table->decimal('jamsostek', 15, 2)
                ->default(0)
                ->after('upah_harian');

            $table->decimal('bpjs_kesehatan', 15, 2)
                ->default(0)
                ->after('jamsostek');

            $table->decimal('bpjs_pensiun', 15, 2)
                ->default(0)
                ->after('bpjs_kesehatan');

            $table->decimal('managemen_fee', 15, 2)
                ->default(0)
                ->after('bpjs_pensiun');

            $table->decimal('grand_total_upah', 15, 2)
                ->default(0)
                ->after('managemen_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajian_harians', function (Blueprint $table) {
            $table->dropColumn([
                'jamsostek',
                'bpjs_kesehatan',
                'bpjs_pensiun',
                'managemen_fee',
                'grand_total_upah',
            ]);
        });
    }
};
