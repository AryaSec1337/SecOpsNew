<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('file_analysis_log_id')->nullable()->after('email_headers');
            $table->unsignedBigInteger('url_analysis_log_id')->nullable()->after('file_analysis_log_id');
            $table->text('analysis_summary')->nullable()->after('url_analysis_log_id');

            $table->foreign('file_analysis_log_id')->references('id')->on('file_analysis_logs')->onDelete('set null');
            $table->foreign('url_analysis_log_id')->references('id')->on('url_analysis_logs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropForeign(['file_analysis_log_id']);
            $table->dropForeign(['url_analysis_log_id']);
            $table->dropColumn(['file_analysis_log_id', 'url_analysis_log_id', 'analysis_summary']);
        });
    }
};
