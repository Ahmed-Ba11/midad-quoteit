<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدار المأثورات: each saying carries a one-line meaning for today's reader, and a link to the page of
 * the classical source where it is documented (e.g. مجمع الأمثال للميداني).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sayings', function (Blueprint $table) {
            $table->string('meaning', 160)->nullable()->after('source');
            $table->string('source_url')->nullable()->after('meaning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sayings', function (Blueprint $table) {
            $table->dropColumn(['meaning', 'source_url']);
        });
    }
};
