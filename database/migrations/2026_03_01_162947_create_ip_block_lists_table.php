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
        Schema::create('ip_block_lists', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->index();
            $table->string('source')->default('Bad IP Alert'); // Where it originated
            $table->text('description')->nullable();            // Context/Rule
            
            // Additional Payload details
            $table->string('dest_ip')->nullable();
            $table->string('dest_port')->nullable();
            $table->string('proto')->nullable();
            $table->string('signature_severity')->nullable();
            
            $table->text('reason')->nullable(); // Administrator edit reason
            
            $table->integer('week_number')->index();
            $table->integer('year')->index();
            $table->string('status')->default('Pending');       // Pending, Blocked, Ignored
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_block_lists');
    }
};
