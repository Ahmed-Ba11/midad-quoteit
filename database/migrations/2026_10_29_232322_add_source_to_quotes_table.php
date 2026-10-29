<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a quoted text comes from (a book, a newspaper, a link). Required whenever the quote names
 * someone else as its author — quoting with the source and author named is what the Saudi
 * copyright law (Art. 15) permits.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('source', 200)->nullable()->after('book');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
