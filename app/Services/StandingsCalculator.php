<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Collection;

class StandingsCalculator
{
    /**
     * Returns a collection of rows sorted by wins desc, then game differential desc:
     * [registration, played, wins, losses, games_won, games_lost]
     */
    public function forRoundRobin(Event $event): Collection
    {
        $registrations = $event->approvedRegistrations()->with(['player', 'partner'])->get();

        $stats = [];
        foreach ($registrations as $r) {
            $stats[$r->id] = [
                'registration' => $r,
                'played' => 0,
                'wins' => 0,
                'losses' => 0,
                'games_won' => 0,
                'games_lost' => 0,
            ];
        }

        $matches = $event->matches()
            ->whereIn('status', ['completed', 'walkover'])
            ->with('games')
            ->get();

        foreach ($matches as $match) {
            foreach ([$match->registration1_id, $match->registration2_id] as $regId) {
                if (! $regId || ! isset($stats[$regId])) {
                    continue;
                }

                $stats[$regId]['played']++;

                if ($match->winner_registration_id === $regId) {
                    $stats[$regId]['wins']++;
                } else {
                    $stats[$regId]['losses']++;
                }
            }

            foreach ($match->games as $game) {
                if (isset($stats[$match->registration1_id])) {
                    $stats[$match->registration1_id]['games_won'] += $game->score1;
                    $stats[$match->registration1_id]['games_lost'] += $game->score2;
                }
                if (isset($stats[$match->registration2_id])) {
                    $stats[$match->registration2_id]['games_won'] += $game->score2;
                    $stats[$match->registration2_id]['games_lost'] += $game->score1;
                }
            }
        }

        $rows = array_values($stats);

        usort($rows, function ($a, $b) {
            if ($a['wins'] !== $b['wins']) {
                return $b['wins'] <=> $a['wins'];
            }

            $diffA = $a['games_won'] - $a['games_lost'];
            $diffB = $b['games_won'] - $b['games_lost'];

            return $diffB <=> $diffA;
        });

        return collect($rows);
    }
}
