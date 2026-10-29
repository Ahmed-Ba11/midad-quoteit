<?php

use App\Models\Quote;
use App\Models\Verse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A normalised copy of each quote's / verse's searchable text (no tashkeel, unified hamza…),
 * so search matches however the reader types. Kept up to date when a quote is saved and when
 * the library is seeded.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->text('search_text')->nullable();
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->text('search_text')->nullable();
        });

        Quote::query()->each(function (Quote $quote) {
            $quote->search_text = $quote->buildSearchText();
            $quote->saveQuietly();
        });
        Verse::with('work.poet')->each(fn (Verse $verse) => $verse->refreshSearchText());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
