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
            $table->string('fullpath')->nullable()->after('server_hostname');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_file_scans', function (Blueprint $table) {
            $table->dropColumn('fullpath');
        });
    }
};
