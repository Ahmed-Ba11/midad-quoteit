<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Library links to Wikisource and other sources are percent-encoded Arabic, so they can pass
 * 255 characters (one saying's link is 332). SQLite never checked the length; MySQL refuses it.
 * Every library link now holds up to 1000 characters.
 */
return new class extends Migration
{
    /**
     * The library's link columns, by table.
     *
     * @var array<string, list<string>>
     */
    private const LINKS = [
        'poets' => ['wikisource_url'],
        'works' => ['wikisource_url'],
        'verses' => ['source_url'],
        'sayings' => ['source_url'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->resize(1000);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->resize(255);
    }

    private function resize(int $length): void
    {
        foreach (self::LINKS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $length) {
                foreach ($columns as $column) {
                    $table->string($column, $length)->nullable()->change();
                }
            });
        }
    }
};
