<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->timestamp('incident_time')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropColumn('incident_time');
        });
    }
};
