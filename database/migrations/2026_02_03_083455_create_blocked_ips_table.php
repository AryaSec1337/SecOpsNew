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
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            // Assuming blocked_by_agent might be null if manually blocked from server for all? 
            // Plan says: "Broadcast specific IPs to relevant agents. If source IP attacked Agent A, Agent A blocks it."
            // So we link to an agent. 
            $table->foreignId('agent_id')->constrained('assets')->onDelete('cascade');
            $table->string('rule_id')->nullable(); // If triggered by a rule
            // Status: pending_block, blocked, pending_unblock, unblocked, failed
            $table->string('status')->default('pending_block'); 
            $table->text('reason')->nullable(); // 'Manual Block' or Rule Description
            $table->timestamps();

            // Index for faster lookups during heartbeat
            $table->index(['agent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
