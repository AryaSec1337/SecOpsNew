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
        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->string('port')->nullable()->after('ip_address');
            $table->string('protocol')->default('tcp')->after('port');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['port', 'protocol', 'user_id']);
        });
    }
};
