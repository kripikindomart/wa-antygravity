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
        Schema::create('auto_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name'); // Rule name
            $table->json('keywords'); // Array of trigger keywords
            $table->enum('match_type', ['exact', 'contains', 'starts_with', 'regex'])->default('contains');
            $table->text('reply_message');
            $table->enum('reply_type', ['text', 'image', 'document'])->default('text');
            $table->string('attachment_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher = checked first
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_replies');
    }
};
