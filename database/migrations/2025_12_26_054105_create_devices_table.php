<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique(); // Unique identifier for Baileys session
            $table->string('name'); // Device label
            $table->string('phone_number')->nullable(); // Filled after QR scan
            $table->enum('status', ['init', 'scanning', 'connected', 'disconnected'])->default('init');
            $table->string('webhook_url')->nullable(); // Optional webhook for incoming messages
            $table->json('session_data')->nullable(); // Store Baileys auth state
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
