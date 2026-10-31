<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * layout — how the words are set: auto (detected), verse (صدر / عجز) or prose (App\Support\QuoteLayout).
 * design — مرسم الحروف's card settings beyond colours and font: background, box, frame, seal…
 *          (whitelisted by QuoteRequest; see config/quotes.php → design). Never HTML.
 * ratio  — a free string now, so the tall story size (9:16) can join wide, square and tall.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('layout', 8)->default('auto')->after('mark_style');
            $table->json('design')->nullable()->after('font_size');
            $table->string('ratio', 8)->default('wide')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['layout', 'design']);
        });
    }
};
