<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('verdict')->nullable(); // CLEAN, SUSPICIOUS, MALICIOUS
            $table->string('status')->default('Pending'); // Pending, In Progress, Resolved
            $table->string('server_hostname')->nullable();
            $table->string('fullpath')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('sha256')->nullable();
            $table->integer('size_bytes')->nullable();
            $table->text('description')->nullable();
            $table->string('detected_by')->nullable(); // YARA, ClamAV, VirusTotal
            $table->json('scan_results')->nullable(); // Stores yara/clamav/vt summary
            $table->foreignId('webhook_file_scan_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_alerts');
    }
};
