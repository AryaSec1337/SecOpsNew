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
        Schema::create('webhook_file_scans', function (Blueprint $table) {
            $table->id();
            $table->string('file_id')->index();
            $table->string('sha256')->index();
            $table->integer('size_bytes')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('verdict')->nullable(); // CLEAN, SUSPICIOUS, MALICIOUS, ERROR
            $table->json('yara_result')->nullable();
            $table->json('clamav_result')->nullable();
            $table->json('vt_result')->nullable();
            $table->json('timestamps_stages')->nullable();
            $table->foreignId('mitigation_log_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_file_scans');
    }
};
