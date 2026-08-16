<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use App\Services\BracketGenerator;
use App\Services\MatchResultRecorder;
use Illuminate\Database\Seeder;

class BadmintonDemoSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@example.com'],
            ['name' => 'Demo Organizer', 'password' => bcrypt('password')]
        );

        $tournament = Tournament::create([
            'name' => 'ShuttleSync Open 2026',
            'slug' => 'shuttlesync-open-2026',
            'description' => "The season-opening club tournament. Men's Singles, Women's Doubles and Mixed Doubles.",
            'venue' => 'Riverside Sports Complex',
            'city' => 'Springfield',
            'start_date' => now()->addWeeks(2),
            'end_date' => now()->addWeeks(2)->addDays(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addWeek(),
            'status' => 'registration_open',
            'created_by' => $organizer->id,
        ]);

        $mensSingles = $tournament->events()->create([
            'name' => "Men's Singles",
            'play_type' => 'singles',
            'category' => 'men',
            'format' => 'single_elimination',
            'points_to_win' => 21,
            'best_of_games' => 3,
        ]);

        $womensDoubles = $tournament->events()->create([
            'name' => "Women's Doubles",
            'play_type' => 'doubles',
            'category' => 'women',
            'format' => 'round_robin',
            'points_to_win' => 21,
            'best_of_games' => 3,
        ]);

        $mixedDoubles = $tournament->events()->create([
            'name' => 'Mixed Doubles',
            'play_type' => 'doubles',
            'category' => 'mixed',
            'format' => 'double_elimination',
            'points_to_win' => 21,
            'best_of_games' => 3,
        ]);

        // Men's Singles: 7 players (one bye), bracket generated and a couple of rounds played.
        $singlesPlayers = Player::factory(7)->create();
        foreach ($singlesPlayers as $i => $player) {
            $mensSingles->registrations()->create([
                'player_id' => $player->id,
                'status' => 'approved',
                'seed' => $i + 1,
            ]);
        }

        app(BracketGenerator::class)->generate($mensSingles->fresh());

        $recorder = app(MatchResultRecorder::class);
        $firstRoundMatches = $mensSingles->matches()->where('round', 1)->where('status', 'ready')->get();
        foreach ($firstRoundMatches as $match) {
            $recorder->record($match, [
                ['score1' => 21, 'score2' => 15],
                ['score1' => 19, 'score2' => 21],
                ['score1' => 21, 'score2' => 18],
            ]);
        }

        // Women's Doubles: 4 pairs (8 players), round robin, no results yet.
        $doublesPlayers = Player::factory(8)->create();
        foreach ($doublesPlayers->chunk(2) as $pair) {
            $womensDoubles->registrations()->create([
                'player_id' => $pair->first()->id,
                'partner_player_id' => $pair->last()->id,
                'status' => 'approved',
            ]);
        }
        app(BracketGenerator::class)->generate($womensDoubles->fresh());

        // Mixed Doubles: 6 pairs approved, but bracket NOT generated yet (shows pending-review workflow).
        $mixedPlayers = Player::factory(12)->create();
        foreach ($mixedPlayers->chunk(2) as $i => $pair) {
            $mixedDoubles->registrations()->create([
                'player_id' => $pair->first()->id,
                'partner_player_id' => $pair->last()->id,
                'status' => $i < 5 ? 'approved' : 'pending',
            ]);
        }

        $this->command?->info('Demo organizer login: organizer@example.com / password');
    }
}
