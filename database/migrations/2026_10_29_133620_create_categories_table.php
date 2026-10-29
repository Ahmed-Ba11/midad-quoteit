<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // The five fixed categories are part of the app, not demo data.
        DB::table('categories')->insert(collect([
            'hikma' => 'حكمة',
            'shir' => 'شعر',
            'adab' => 'أدب',
            'falsafa' => 'فلسفة',
            'taammul' => 'تأمل',
        ])->map(fn (string $name, string $slug) => [
            'name' => $name,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
