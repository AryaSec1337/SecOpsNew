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
        Schema::create('email_analysis_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->string('sender')->nullable();
            $table->string('recipient')->nullable();
            $table->integer('score')->default(0);
            $table->string('risk_level')->default('Low');
            $table->json('results')->nullable(); // Stores detailed analysis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_analysis_histories');
    }
};
