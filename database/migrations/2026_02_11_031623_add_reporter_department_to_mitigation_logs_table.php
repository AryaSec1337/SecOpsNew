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
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->string('reporter_department')->nullable()->after('reporter_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropColumn('reporter_department');
        });
    }
};
