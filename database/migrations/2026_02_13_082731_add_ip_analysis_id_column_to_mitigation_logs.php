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
        if (!Schema::hasColumn('mitigation_logs', 'ip_analysis_id')) {
            Schema::table('mitigation_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('ip_analysis_id')->nullable()->after('url_analysis_log_id');
                $table->foreign('ip_analysis_id')->references('id')->on('ip_analyses')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropForeign(['ip_analysis_id']);
            $table->dropColumn('ip_analysis_id');
        });
    }
};
