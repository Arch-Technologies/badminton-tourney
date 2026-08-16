<?php

namespace App\Services;

use App\Models\GameMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchResultRecorder
{
    public function __construct(private BracketProgression $progression) {}

    /**
     * @param  array<int, array{score1:int, score2:int}>  $games  1-indexed by game number
     */
    public function record(GameMatch $match, array $games): GameMatch
    {
        if (! $match->hasBothParticipants()) {
            throw ValidationException::withMessages(['games' => 'Both participants must be set before recording a result.']);
        }

        $bestOf = $match->event->best_of_games;
        $needed = (int) ceil($bestOf / 2);

        $wins1 = 0;
        $wins2 = 0;

        foreach ($games as $number => $game) {
            $this->validateGameScore((int) $game['score1'], (int) $game['score2']);

            if ($game['score1'] > $game['score2']) {
                $wins1++;
            } else {
                $wins2++;
            }

            if ($wins1 === $needed || $wins2 === $needed) {
                break;
            }
        }

        if ($wins1 !== $needed && $wins2 !== $needed) {
            throw ValidationException::withMessages(['games' => "Neither side has reached {$needed} games won yet."]);
        }

        $winnerIsOne = $wins1 === $needed;

        DB::transaction(function () use ($match, $games, $winnerIsOne) {
            $match->games()->delete();

            $gameNumber = 1;
            foreach ($games as $game) {
                $match->games()->create([
                    'game_number' => $gameNumber++,
                    'score1' => $game['score1'],
                    'score2' => $game['score2'],
                ]);
            }

            $winnerId = $winnerIsOne ? $match->registration1_id : $match->registration2_id;

            $match->update([
                'winner_registration_id' => $winnerId,
                'status' => 'completed',
            ]);

            $match->refresh();

            $resetCreated = $this->handleGrandFinalReset($match);
            $this->progression->propagateLoserIfApplicable($match);

            if (! $resetCreated) {
                $this->progression->propagateWinner($match);
            }
        });

        return $match->fresh();
    }

    private function validateGameScore(int $score1, int $score2): void
    {
        if ($score1 === $score2) {
            throw ValidationException::withMessages(['games' => 'A game cannot end in a tie.']);
        }

        foreach ([$score1, $score2] as $s) {
            if ($s < 0 || $s > 30) {
                throw ValidationException::withMessages(['games' => 'Game scores must be between 0 and 30.']);
            }
        }

        $max = max($score1, $score2);
        $min = min($score1, $score2);

        if ($max < 21) {
            throw ValidationException::withMessages(['games' => 'The winner of a game must score at least 21.']);
        }

        if ($max < 30 && ($max - $min) < 2) {
            throw ValidationException::withMessages(['games' => 'A game must be won by at least 2 points (unless the score is 30).']);
        }
    }

    /**
     * In double elimination, if the loser's-bracket entrant wins the Grand Final,
     * both sides now have one loss each, so a single reset match decides the title.
     */
    private function handleGrandFinalReset(GameMatch $match): bool
    {
        if ($match->bracket_type !== 'final' || $match->round !== 1) {
            return false;
        }

        if ($match->winner_registration_id !== $match->registration2_id) {
            return false; // winners-bracket side won outright, no reset needed
        }

        $exists = GameMatch::where('event_id', $match->event_id)
            ->where('bracket_type', 'final')
            ->where('round', 2)
            ->exists();

        if ($exists) {
            return false;
        }

        GameMatch::create([
            'event_id' => $match->event_id,
            'bracket_type' => 'final',
            'round' => 2,
            'match_number' => 1,
            'registration1_id' => $match->registration1_id,
            'registration2_id' => $match->registration2_id,
            'status' => 'ready',
        ]);

        return true;
    }
}
