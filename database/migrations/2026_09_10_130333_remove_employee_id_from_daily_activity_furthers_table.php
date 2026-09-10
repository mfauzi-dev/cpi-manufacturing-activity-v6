<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activity_furthers', function (Blueprint $table) {
            $table->dropForeign('daily_activity_furthers_employee_id_foreign');
            $table->dropColumn('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('daily_activity_furthers', function (Blueprint $table) {
            $table->foreignId('employee_id')
                ->nullable()
                ->after('line_id')
                ->constrained('employees')
                ->cascadeOnDelete();
        });
    }
};