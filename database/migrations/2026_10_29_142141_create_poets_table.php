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
        Schema::create('poets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug')->unique();
            $table->enum('era', ['jahili', 'mukhadram', 'umawi', 'abbasi']);
            $table->string('years', 40);
            $table->string('region', 40)->nullable();
            $table->string('bio', 400);
            $table->boolean('is_peninsula')->default(false);
            $table->string('color', 7);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poets');
    }
};
