<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ركني: the reader's own colour (a palette key from config/quotes.php) and one quote pinned at the top.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('accent', 12)->nullable()->after('bio');
            $table->foreignId('pinned_quote_id')->nullable()->after('accent')->constrained('quotes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pinned_quote_id');
            $table->dropColumn('accent');
        });
    }
};
