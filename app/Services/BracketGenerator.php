<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GameMatch;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BracketGenerator
{
    public function __construct(private BracketProgression $progression) {}

    public function generate(Event $event): void
    {
        $registrations = $event->approvedRegistrations()
            ->get()
            ->sortBy([['seed', 'asc']])
            ->values();

        // Registrations without a seed keep insertion order after the seeded ones;
        // shuffle only the unseeded group so repeated generation isn't deterministic.
        $seeded = $registrations->filter(fn ($r) => $r->seed !== null)->values();
        $unseeded = $registrations->filter(fn ($r) => $r->seed === null)->shuffle()->values();
        $ordered = $seeded->concat($unseeded)->values();

        if ($ordered->count() < 2) {
            throw new RuntimeException('At least 2 approved registrations are required to generate a bracket.');
        }

        DB::transaction(function () use ($event, $ordered) {
            $event->matches()->delete();

            match ($event->format) {
                'single_elimination' => $this->generateSingleElimination($event, $ordered),
                'double_elimination' => $this->generateDoubleElimination($event, $ordered),
                'round_robin' => $this->generateRoundRobin($event, $ordered),
            };

            $event->update(['status' => 'in_progress']);
        });
    }

    /**
     * Classic bracket seed order, e.g. for 8 slots: [1, 8, 4, 5, 2, 7, 3, 6].
     * Returns an array (0-indexed) of seed ranks (1 = best) for each bracket slot.
     */
    private function seedOrder(int $bracketSize): array
    {
        $seeds = [1];

        while (count($seeds) < $bracketSize) {
            $m = count($seeds) * 2;
            $new = [];
            foreach ($seeds as $s) {
                $new[] = $s;
                $new[] = $m + 1 - $s;
            }
            $seeds = $new;
        }

        return $seeds;
    }

    private function slotsForBracket($ordered): array
    {
        $count = $ordered->count();
        $bracketSize = 2 ** (int) ceil(log($count, 2));
        $order = $this->seedOrder($bracketSize);

        $bySeed = [];
        foreach ($ordered->values() as $i => $registration) {
            $bySeed[$i + 1] = $registration;
        }

        $slots = [];
        foreach ($order as $i => $seedRank) {
            $slots[$i] = $bySeed[$seedRank] ?? null; // null = bye
        }

        return [$bracketSize, $slots];
    }

    private function generateSingleElimination(Event $event, $ordered): void
    {
        [$bracketSize, $slots] = $this->slotsForBracket($ordered);
        $rounds = (int) log($bracketSize, 2);

        $byRound = $this->createEmptyRounds($event, 'winners', $bracketSize, $rounds);
        $this->linkSequentialRounds($byRound, $rounds);
        $this->seedFirstRound($byRound[1], $slots);
    }

    private function generateDoubleElimination(Event $event, $ordered): void
    {
        [$bracketSize, $slots] = $this->slotsForBracket($ordered);
        $k = (int) log($bracketSize, 2);

        if ($k < 2) {
            throw new RuntimeException('Double elimination needs at least 4 approved registrations.');
        }

        $winners = $this->createEmptyRounds($event, 'winners', $bracketSize, $k);
        $this->linkSequentialRounds($winners, $k);

        $losersRoundsCount = 2 * ($k - 1);
        $losers = [];
        $currentCount = $bracketSize / 4;

        $losers[1] = $this->createMatches($event, 'losers', 1, max($currentCount, 1));

        // WR1 losers feed directly into LR1.
        foreach ($winners[1] as $m => $match) {
            $lrMatch = $losers[1][(int) ceil($m / 2)];
            $slot = $m % 2 === 1 ? 1 : 2;
            $match->update([
                'loser_next_match_id' => $lrMatch->id,
                'loser_next_match_slot' => $slot,
            ]);
        }

        for ($j = 2; $j <= $losersRoundsCount; $j++) {
            if ($j % 2 === 0) {
                // Pair previous LR-round winners against fresh losers dropping from the winners bracket.
                $matchCount = $currentCount;
                $losers[$j] = $this->createMatches($event, 'losers', $j, $matchCount);

                foreach ($losers[$j - 1] as $m => $prevMatch) {
                    $target = $losers[$j][$m];
                    $prevMatch->update(['next_match_id' => $target->id, 'next_match_slot' => 1]);
                }

                $wrRound = (int) ($j / 2) + 1;
                foreach ($winners[$wrRound] as $m => $wrMatch) {
                    $target = $losers[$j][$m];
                    $wrMatch->update(['loser_next_match_id' => $target->id, 'loser_next_match_slot' => 2]);
                }
            } else {
                // Consolidate: pair up the previous round's winners against each other.
                $matchCount = max((int) ($currentCount / 2), 1);
                $losers[$j] = $this->createMatches($event, 'losers', $j, $matchCount);

                foreach ($losers[$j - 1] as $m => $prevMatch) {
                    $target = $losers[$j][(int) ceil($m / 2)];
                    $slot = $m % 2 === 1 ? 1 : 2;
                    $prevMatch->update(['next_match_id' => $target->id, 'next_match_slot' => $slot]);
                }

                $currentCount = $matchCount;
            }
        }

        $grandFinal = GameMatch::create([
            'event_id' => $event->id,
            'bracket_type' => 'final',
            'round' => 1,
            'match_number' => 1,
            'status' => 'pending',
        ]);

        $winnersFinal = $winners[$k][1];
        $winnersFinal->update(['next_match_id' => $grandFinal->id, 'next_match_slot' => 1]);

        $losersFinal = $losers[$losersRoundsCount][1];
        $losersFinal->update(['next_match_id' => $grandFinal->id, 'next_match_slot' => 2]);

        // Seed round 1 last: every next_match_id / loser_next_match_id link in both
        // brackets must already exist before byes start cascading through them.
        $this->seedFirstRound($winners[1], $slots);
    }

    private function generateRoundRobin(Event $event, $ordered): void
    {
        $ids = $ordered->pluck('id')->values()->all();

        if (count($ids) % 2 !== 0) {
            $ids[] = null; // bye
        }

        $n = count($ids);
        $totalRounds = $n - 1;

        for ($round = 1; $round <= $totalRounds; $round++) {
            $matchNumber = 1;

            for ($i = 0; $i < $n / 2; $i++) {
                $a = $ids[$i];
                $b = $ids[$n - 1 - $i];

                if ($a !== null && $b !== null) {
                    GameMatch::create([
                        'event_id' => $event->id,
                        'bracket_type' => 'round_robin',
                        'round' => $round,
                        'match_number' => $matchNumber++,
                        'registration1_id' => $a,
                        'registration2_id' => $b,
                        'status' => 'ready',
                    ]);
                }
            }

            // Rotate all but the first fixed element.
            $fixed = $ids[0];
            $rest = array_slice($ids, 1);
            array_unshift($rest, array_pop($rest));
            $ids = array_merge([$fixed], $rest);
        }
    }

    private function createEmptyRounds(Event $event, string $bracketType, int $bracketSize, int $rounds): array
    {
        $byRound = [];

        for ($round = 1; $round <= $rounds; $round++) {
            $numMatches = $bracketSize / (2 ** $round);
            $byRound[$round] = $this->createMatches($event, $bracketType, $round, $numMatches);
        }

        return $byRound;
    }

    private function createMatches(Event $event, string $bracketType, int $round, int $count): array
    {
        $matches = [];

        for ($m = 1; $m <= $count; $m++) {
            $matches[$m] = GameMatch::create([
                'event_id' => $event->id,
                'bracket_type' => $bracketType,
                'round' => $round,
                'match_number' => $m,
                'status' => 'pending',
            ]);
        }

        return $matches;
    }

    private function linkSequentialRounds(array $byRound, int $rounds): void
    {
        for ($round = 1; $round < $rounds; $round++) {
            foreach ($byRound[$round] as $m => $match) {
                $next = $byRound[$round + 1][(int) ceil($m / 2)];
                $slot = $m % 2 === 1 ? 1 : 2;
                $match->update(['next_match_id' => $next->id, 'next_match_slot' => $slot]);
            }
        }
    }

    private function seedFirstRound(array $firstRoundMatches, array $slots): void
    {
        foreach ($firstRoundMatches as $m => $match) {
            $slotA = $slots[($m - 1) * 2] ?? null;
            $slotB = $slots[($m - 1) * 2 + 1] ?? null;

            $match->update([
                'registration1_id' => $slotA?->id,
                'registration2_id' => $slotB?->id,
                'status' => 'ready',
            ]);

            $this->progression->maybeSettle($match->fresh());
        }
    }
}
