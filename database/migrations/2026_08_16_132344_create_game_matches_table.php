<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->enum('bracket_type', ['winners', 'losers', 'final', 'round_robin'])->default('winners');
            $table->unsignedInteger('round');
            $table->unsignedInteger('match_number');
            $table->foreignId('registration1_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('registration2_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('winner_registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('next_match_id')->nullable()->constrained('game_matches')->nullOnDelete();
            $table->unsignedTinyInteger('next_match_slot')->nullable();
            $table->foreignId('loser_next_match_id')->nullable()->constrained('game_matches')->nullOnDelete();
            $table->unsignedTinyInteger('loser_next_match_slot')->nullable();
            $table->string('court')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->enum('status', ['pending', 'ready', 'in_progress', 'completed', 'walkover'])->default('pending');
            $table->timestamps();

            $table->index(['event_id', 'bracket_type', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};
