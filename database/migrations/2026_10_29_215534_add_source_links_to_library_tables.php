<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where to read on: the library shows selected verses, and links out to the full poem / diwan
 * (Wikisource is the licensed source of our text; aldiwan.net is linked, never copied).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('poets', function (Blueprint $table) {
            $table->string('wikisource_url')->nullable();
            $table->string('aldiwan_url')->nullable();
        });

        Schema::table('works', function (Blueprint $table) {
            $table->string('wikisource_url')->nullable();
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->string('source_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poets', function (Blueprint $table) {
            $table->dropColumn(['wikisource_url', 'aldiwan_url']);
        });

        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn('wikisource_url');
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
    }
};
