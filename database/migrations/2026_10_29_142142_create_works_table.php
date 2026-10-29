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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poet_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('slug')->unique();
            $table->enum('type', ['diwan', 'muallaqa', 'collection']);
            $table->string('color', 7);
            $table->string('ink', 7);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
