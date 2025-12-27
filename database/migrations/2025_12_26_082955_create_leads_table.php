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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('number');
            $table->string('source')->default('incoming'); // incoming, broadcast_reply
            $table->string('status')->default('new'); // new, follow_up, converted, lost
            $table->text('last_message')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            // Index for daily queries and uniqueness check
            $table->index(['user_id', 'created_at']);
            $table->index('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
