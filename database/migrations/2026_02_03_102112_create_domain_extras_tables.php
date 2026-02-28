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
        Schema::create('domain_ssl_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('issuer')->nullable();
            $table->string('subject')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->integer('days_remaining')->default(0);
            $table->timestamps();
        });

        Schema::create('domain_dns_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('record_type'); // A, MX, NS, TXT
            $table->text('value');
            $table->string('hash'); // To detect changes
            $table->timestamps();
        });

        Schema::create('typosquat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('original_domain');
            $table->string('permuted_domain');
            $table->string('ip_address')->nullable(); // If active
            $table->string('mx_record')->nullable(); // If likely receiving mail
            $table->boolean('is_registered')->default(false);
            $table->timestamp('scan_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typosquat_logs');
        Schema::dropIfExists('domain_dns_records');
        Schema::dropIfExists('domain_ssl_statuses');
    }
};
