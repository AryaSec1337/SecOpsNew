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
        Schema::create('bad_ip_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('rule_description');
            $table->string('src_ip');
            $table->string('dest_ip')->nullable();
            $table->string('dest_port')->nullable();
            $table->string('proto')->nullable();
            $table->string('signature_severity')->nullable();
            $table->json('raw_data')->nullable(); // Original wazuh document
            $table->string('status')->default('New'); // New, Acknowledged, Resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_ip_alerts');
    }
};
