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
        Schema::create('ip_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->integer('risk_score')->default(0);
            $table->json('geo_data')->nullable(); // Country, Region, ASN
            $table->json('virustotal_data')->nullable(); // Stats, engines
            $table->json('abuseipdb_data')->nullable(); // Score, reports
            $table->json('greynoise_data')->nullable(); // Classification, tags
            $table->json('alienvault_data')->nullable(); // Pulses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_analyses');
    }
};
