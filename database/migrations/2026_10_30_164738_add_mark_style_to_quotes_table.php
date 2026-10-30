<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a text-only quote (presentation = text) is framed: «guillemets», “curly” marks,
 * gold ornaments, or an arch like the رقيم tablet. Keys live in config/quotes.php → marks.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('mark_style', 12)->default('guillemets')->after('presentation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('mark_style');
        });
    }
};
