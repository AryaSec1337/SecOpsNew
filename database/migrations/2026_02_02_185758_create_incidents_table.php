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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('severity')->default('Medium'); // Low, Medium, High, Critical
            $table->string('status')->default('Open'); // Open, Investigating, Resolved, False Positive
            $table->text('description')->nullable();
            
            // Polymorphic relation to the source log (ActivityLog or FileIntegrityLog)
            $table->unsignedBigInteger('source_id');
            $table->string('source_type');
            
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();
            
            $table->index(['source_id', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
