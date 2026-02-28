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
        Schema::create('ransomware_victims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('group_name');
            $table->string('post_title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('post_url')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->string('country')->nullable();
            $table->string('activity')->nullable();
            $table->json('infostealer_data')->nullable(); // Stores user/employee counts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ransomware_victims');
    }
};
