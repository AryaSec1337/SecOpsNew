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
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->nullable()->after('status');
            $table->enum('severity', ['Low', 'Medium', 'High', 'Critical'])->nullable()->after('priority');
            $table->string('hostname')->nullable()->after('description');
            $table->string('internal_ip')->nullable()->after('hostname');
            $table->string('os')->nullable()->after('internal_ip');
            $table->string('network_zone')->nullable()->after('os');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropColumn(['priority', 'severity', 'hostname', 'internal_ip', 'os', 'network_zone']);
        });
    }
};
