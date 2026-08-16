<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->unsignedInteger('seed')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
