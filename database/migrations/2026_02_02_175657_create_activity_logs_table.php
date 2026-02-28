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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('timestamp');
            $table->string('status_code');
            $table->string('method');
            $table->text('path');
            $table->string('ip_address')->nullable(); // Client IP
            $table->string('agent_name')->nullable();
            $table->string('agent_ip')->nullable();
            $table->string('os')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('log_file')->nullable(); // Path logs berasal
            $table->json('details')->nullable(); // Rule match details
            $table->bigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
