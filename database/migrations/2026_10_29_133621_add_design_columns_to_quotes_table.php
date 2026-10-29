<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('author', 100)->nullable();
            $table->string('book', 150)->nullable();
            $table->string('story', 500)->nullable();
            $table->string('font', 20)->default('amiri');
            $table->string('bg_color', 7)->default('#E8E1D5');
            $table->string('text_color', 7)->default('#1E1A17');
            $table->string('highlight_color', 7)->default('#8A3324');
            $table->enum('highlight_style', ['color', 'mark'])->default('mark');
            $table->json('highlights')->nullable();
            $table->boolean('texture')->default(true);
            $table->enum('ratio', ['wide', 'square', 'tall'])->default('wide');
            $table->unsignedTinyInteger('font_size')->default(34);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'author', 'book', 'story', 'font', 'bg_color', 'text_color', 'highlight_color',
                'highlight_style', 'highlights', 'texture', 'ratio', 'font_size',
            ]);
        });
    }
};
