<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('game_matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('game_number');
            $table->unsignedTinyInteger('score1')->default(0);
            $table->unsignedTinyInteger('score2')->default(0);
            $table->timestamps();

            $table->unique(['match_id', 'game_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
