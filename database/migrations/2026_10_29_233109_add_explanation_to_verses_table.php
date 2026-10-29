<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "شرح البيت": a plain-language meaning (written for مِداد) and the verse's difficult words,
 * each with its meaning checked against لسان العرب and a link to the entry.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('verses', function (Blueprint $table) {
            $table->text('explanation')->nullable();
            $table->json('glossary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verses', function (Blueprint $table) {
            $table->dropColumn(['explanation', 'glossary']);
        });
    }
};
