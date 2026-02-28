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
        Schema::create('file_integrity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('change_type'); // Modified, Created, Deleted
            $table->string('process_name')->nullable(); // e.g., nginx, sshd
            $table->string('user')->nullable(); // whodunnit
            $table->string('hash_before')->nullable();
            $table->string('hash_after')->nullable();
            $table->string('severity')->default('Medium'); // Low, Medium, High, Critical
            $table->timestamp('detected_at');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_integrity_logs');
    }
};
