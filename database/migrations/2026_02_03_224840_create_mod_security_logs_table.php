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
        Schema::create('mod_security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('client_ip');
            $table->text('uri');
            $table->string('method')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('rule_matches')->nullable(); // Stores array of triggered rules {id, msg, data}
            $table->json('raw_log'); // Complete JSON payload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_security_logs');
    }
};
