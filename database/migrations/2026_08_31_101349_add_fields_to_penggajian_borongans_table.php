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
        Schema::table('penggajian_borongans', function (Blueprint $table) {
             $table->unsignedSmallInteger('total_hari_kerja')
                ->default(0)
                ->after('total_kg');

            $table->decimal('jamsostek', 15, 2)
                ->default(0)
                ->after('total_upah');

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
        Schema::table('penggajian_borongans', function (Blueprint $table) {
            $table->dropColumn([
                'total_hari_kerja',
                'jamsostek',
                'bpjs_kesehatan',
                'bpjs_pensiun',
                'managemen_fee',
                'grand_total_upah',
            ]);
        });
    }
};
