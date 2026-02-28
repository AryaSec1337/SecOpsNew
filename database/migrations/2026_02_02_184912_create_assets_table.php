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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Hostname for Server, App Name for Application
            $table->string('type')->index(); // 'server', 'application'
            $table->string('ip_address')->nullable(); // For Server
            $table->string('os_name')->nullable(); // For Server
            $table->string('os_version')->nullable(); // For Server
            $table->string('role')->nullable(); // For Server (e.g. Database, Web)
            
            $table->string('app_version')->nullable(); // For App
            $table->string('vendor')->nullable(); // For App
            $table->string('app_type')->nullable(); // For App (SaaS, Enterprise, etc)
            $table->string('criticality')->nullable(); // For App (High, Medium, Critical)
            $table->string('owner')->nullable(); // For App
            
            $table->string('location')->nullable(); // Data Center A, B, etc.
            $table->string('status')->default('Offline'); // Online, Offline, Maintenance
            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
