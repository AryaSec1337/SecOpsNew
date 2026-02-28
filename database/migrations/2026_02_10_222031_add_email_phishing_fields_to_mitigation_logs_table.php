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
            $table->string('type')->default('General')->after('id'); // General, Email Phishing
            $table->string('email_subject')->nullable()->after('type');
            $table->string('email_sender')->nullable()->after('email_subject');
            $table->string('email_recipient')->nullable()->after('email_sender');
            $table->text('email_headers')->nullable()->after('email_recipient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitigation_logs', function (Blueprint $table) {
            $table->dropColumn(['type', 'email_subject', 'email_sender', 'email_recipient', 'email_headers']);
        });
    }
};
