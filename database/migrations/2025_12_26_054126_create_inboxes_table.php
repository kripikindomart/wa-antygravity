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
        Schema::create('inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('from_number');
            $table->string('from_name')->nullable();
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'image', 'document', 'video', 'audio', 'sticker', 'location'])->default('text');
            $table->string('media_url')->nullable();
            $table->string('wa_message_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index('device_id');
            $table->index('from_number');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inboxes');
    }
};
