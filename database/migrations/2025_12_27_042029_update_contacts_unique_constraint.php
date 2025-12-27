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
        Schema::table('contacts', function (Blueprint $table) {
            // Drop the old unique constraint (user_id + phone_number)
            $table->dropUnique('contacts_user_id_phone_number_unique');

            // Add new unique constraint (user_id + phone_number + contact_group_id)
            // This allows the same phone number to exist in different groups
            $table->unique(['user_id', 'phone_number', 'contact_group_id'], 'contacts_user_phone_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Remove the new constraint
            $table->dropUnique('contacts_user_phone_group_unique');

            // Restore the old constraint
            $table->unique(['user_id', 'phone_number'], 'contacts_user_id_phone_number_unique');
        });
    }
};
