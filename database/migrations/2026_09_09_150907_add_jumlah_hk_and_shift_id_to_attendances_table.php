<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('jumlah_hk', 8, 2)
                ->nullable()
                ->after('status');

            $table->foreignId('shift_id')
                ->nullable()
                ->after('jumlah_hk')
                ->constrained('shifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'jumlah_hk']);
        });
    }
};