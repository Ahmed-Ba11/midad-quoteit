<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A quote is shown as a designed card, or as plain text between quotation marks.
 * A reader decides whether visitors can see the poets they keep (شعرائي).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('presentation', 8)->default('card')->after('is_public');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_poets')->default(true)->after('accent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('presentation');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_poets');
        });
    }
};
