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
        Schema::create('domain_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->timestamp('scan_date');
            $table->integer('reputation_score')->default(0);
            $table->json('vt_stats')->nullable(); // malicious, harmless, suspicious, timeout
            $table->string('permalink')->nullable(); // VT Report Link
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_scan_logs');
    }
};
