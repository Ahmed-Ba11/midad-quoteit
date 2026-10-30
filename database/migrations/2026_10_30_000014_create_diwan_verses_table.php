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
        Schema::create('diwan_verses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diwan_id')->constrained()->cascadeOnDelete();
            $table->string('sadr', 120);
            $table->string('ajuz', 120)->nullable();
            $table->string('note', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diwan_verses');
    }
};
