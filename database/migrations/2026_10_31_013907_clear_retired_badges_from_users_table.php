<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Five badges were retired (الشمسة، الهلال، الدواة، النخلة، حرف الضاد); readers who wore one go back to their initial.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('badge', ['shamsa', 'crescent', 'inkwell', 'palm', 'dad'])
            ->update(['badge' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The old choices are not kept, so there is nothing to restore.
    }
};
