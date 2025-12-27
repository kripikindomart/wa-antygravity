<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('audience_type')->default('group')->after('name'); // group, import
            // schedule_at already exists as scheduled_at
            $table->string('error_mode')->default('continue')->after('scheduled_at'); // continue, stop
            $table->json('mapping_config')->nullable()->after('error_mode'); // Store CSV mapping
        });

        // Create campaign_recipients table
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete(); // Link to existing contact if matched
            $table->string('phone_number');
            $table->string('name')->nullable();
            $table->json('custom_data')->nullable(); // For dynamic variables
            $table->string('status')->default('pending'); // pending, processing, sent, failed
            $table->text('error_message')->nullable();
            $table->string('wa_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['type', 'schedule_at', 'error_mode', 'mapping_config']);
        });
    }
};
