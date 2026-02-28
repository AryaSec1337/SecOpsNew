<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wazuh_alerts', function (Blueprint $table) {
            $table->id();

            // Wazuh core identifiers
            $table->string('alert_id')->nullable()->index();       // Wazuh alert ID (e.g. "1614556789.123456")
            $table->string('timestamp_wazuh')->nullable();          // Original Wazuh timestamp

            // Rule info (indexed for filtering)
            $table->string('rule_id')->nullable()->index();
            $table->integer('rule_level')->default(0)->index();
            $table->text('rule_description')->nullable();
            $table->json('rule_groups')->nullable();                // ["syslog","sshd","authentication_failed"]
            $table->json('rule_mitre')->nullable();                 // MITRE ATT&CK mapping

            // Agent info
            $table->string('agent_id')->nullable()->index();
            $table->string('agent_name')->nullable();
            $table->string('agent_ip')->nullable();

            // Manager info
            $table->string('manager_name')->nullable();

            // Source/Destination network info
            $table->string('src_ip')->nullable()->index();
            $table->string('src_port')->nullable();
            $table->string('dst_ip')->nullable();
            $table->string('dst_port')->nullable();
            $table->string('src_user')->nullable();
            $table->string('dst_user')->nullable();

            // Log details
            $table->text('full_log')->nullable();
            $table->string('location')->nullable();
            $table->string('decoder_name')->nullable();

            // SyscheckFIM (File Integrity Monitoring)
            $table->json('syscheck')->nullable();

            // The full raw Wazuh alert JSON — captures ALL fields
            $table->json('raw_json')->nullable();

            // SecOps management
            $table->string('status')->default('New')->index();  // New, Acknowledged, Resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wazuh_alerts');
    }
};
