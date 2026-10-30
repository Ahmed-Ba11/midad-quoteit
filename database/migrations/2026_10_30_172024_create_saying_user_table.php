<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جداري: the sayings a reader hangs on their own wall in ركني (shown to visitors only if they allow it).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saying_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saying_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'saying_id']);
            $table->index('saying_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_wall')->default(true)->after('show_poets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_wall');
        });

        Schema::dropIfExists('saying_user');
    }
};
