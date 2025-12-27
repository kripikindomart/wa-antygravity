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
        // Fix Messages Access
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // Fix Campaigns Access
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // Fix Auto Replies Access
        Schema::table('auto_replies', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // Fix Leads Access
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse strictly for this fix
    }
};
