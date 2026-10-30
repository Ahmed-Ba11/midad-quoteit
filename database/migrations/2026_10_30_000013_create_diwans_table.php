<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دواوين القرّاء: a poet and the verses a reader copied out with their source. Kept apart from the
 * verified خزانة الأدب. A diwan can be public only when the poet died more than 50 years ago
 * (Saudi copyright law, Art. 19); otherwise it stays a private notebook.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diwans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('poet_name', 60);
            $table->unsignedSmallInteger('poet_death_year')->nullable();
            $table->string('title', 80);
            $table->string('about', 300)->nullable();
            $table->string('source', 200);
            $table->string('color', 12)->default('cocoa');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['is_public', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diwans');
    }
};
