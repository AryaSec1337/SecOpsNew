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
        Schema::create('mitigation_log_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitigation_log_id')->constrained('mitigation_logs')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('file_type')->nullable(); // e.g., 'evidence', 'attachment'
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitigation_log_files');
    }
};
