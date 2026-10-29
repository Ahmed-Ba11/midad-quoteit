<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite does not index foreign keys on its own. These cover the queries every quotes page runs:
 * newest first, a user's quotes, and the like / reply / bookmark counts per quote.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('user_id');
            $table->index('category_id');
        });

        foreach (['likes', 'bookmarks', 'replies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->index('quote_id');
            });
        }

        Schema::table('works', function (Blueprint $table) {
            $table->index('poet_id');
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->index('work_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['category_id']);
        });

        foreach (['likes', 'bookmarks', 'replies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['quote_id']);
            });
        }

        Schema::table('works', function (Blueprint $table) {
            $table->dropIndex(['poet_id']);
        });

        Schema::table('verses', function (Blueprint $table) {
            $table->dropIndex(['work_id']);
        });
    }
};
