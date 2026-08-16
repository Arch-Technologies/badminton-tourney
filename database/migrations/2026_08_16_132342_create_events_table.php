<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('play_type', ['singles', 'doubles']);
            $table->enum('category', ['men', 'women', 'mixed', 'open']);
            $table->enum('format', ['single_elimination', 'double_elimination', 'round_robin']);
            $table->unsignedInteger('max_participants')->nullable();
            $table->unsignedTinyInteger('points_to_win')->default(21);
            $table->unsignedTinyInteger('best_of_games')->default(3);
            $table->enum('status', ['pending', 'seeded', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();

            $table->unique(['tournament_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
