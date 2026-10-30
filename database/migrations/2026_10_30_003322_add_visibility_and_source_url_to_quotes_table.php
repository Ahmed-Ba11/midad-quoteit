<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who sees a quote (public, or only in its owner's corner), and an optional link to read on.
 * The link is kept apart from the source's name, so readers see "the diwan's name", not a raw URL.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('is_original');
            $table->string('source_url', 255)->nullable()->after('source');

            $table->index(['is_public', 'created_at']);
        });

        // Sources that were only a link become the link; the name is left for the owner to add.
        DB::table('quotes')->where('source', 'like', 'http%')->update([
            'source_url' => DB::raw('source'),
            'source' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['is_public', 'created_at']);
            $table->dropColumn(['is_public', 'source_url']);
        });
    }
};
