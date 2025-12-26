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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->json('metadata')->nullable(); // Additional custom fields
            $table->timestamps();

            $table->index('user_id');
            $table->index('phone_number');
            $table->unique(['user_id', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
