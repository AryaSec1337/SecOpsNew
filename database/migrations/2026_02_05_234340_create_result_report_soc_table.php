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
        Schema::create('result_report_soc', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->json('ip_info')->nullable();
            $table->json('greynoise')->nullable();
            $table->json('virustotal')->nullable();
            $table->json('abuseipdb')->nullable();
            $table->json('alienvault')->nullable();
            $table->longText('ai_analysis')->nullable();
            $table->integer('risk_score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_report_soc');
    }
};
