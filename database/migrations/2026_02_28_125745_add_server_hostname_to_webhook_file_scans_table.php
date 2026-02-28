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
        Schema::table('webhook_file_scans', function (Blueprint $table) {
            $table->string('server_hostname')->nullable()->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_file_scans', function (Blueprint $table) {
            $table->dropColumn('server_hostname');
        });
    }
};
