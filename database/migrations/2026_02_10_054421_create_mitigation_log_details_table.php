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
        Schema::create('mitigation_log_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitigation_log_id')->constrained()->onDelete('cascade');
            $table->string('action'); // e.g., "Patch Applied", "Conf Changed"
            $table->text('description')->nullable();
            $table->dateTime('log_date')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitigation_log_details');
    }
};
