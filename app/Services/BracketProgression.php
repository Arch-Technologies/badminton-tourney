<?php

namespace App\Services;

use App\Models\GameMatch;

/**
 * Shared winner/loser advancement logic used both when a bracket is first
 * generated (to auto-resolve byes) and after a real result is recorded.
 *
 * A slot in round 1 is either filled or a permanent bye (no predecessor match).
 * A slot in round 2+ is always fed by a predecessor match, so a null slot there
 * just means "still waiting" until that predecessor is proven no longer able to
 * fill it — which we can only know once *all* of a match's predecessors have
 * reached a terminal status (completed/walkover). Resolving a slot the moment
 * only one predecessor is done — before its sibling has even been created a
 * winner — would incorrectly treat a normal in-progress match as a bye.
 */
class BracketProgression
{
    /**
     * Re-evaluate a match after one of its predecessors changed state:
     * mark it ready once both slots are filled, or resolve it as a walkover
     * (possibly with no winner at all, in rare multi-bye cascades) once every
     * predecessor has concluded and it still can't be fully filled.
     */
    public function maybeSettle(GameMatch $match): void
    {
        if (in_array($match->status, ['completed', 'walkover'], true)) {
            return;
        }

        if ($match->hasBothParticipants()) {
            $match->update(['status' => 'ready']);

            return;
        }

        $predecessors = GameMatch::where('next_match_id', $match->id)
            ->orWhere('loser_next_match_id', $match->id)
            ->get();

        $allPredecessorsDecided = $predecessors->every(
            fn ($p) => in_array($p->status, ['completed', 'walkover'], true)
        );

        if (! $allPredecessorsDecided) {
            return; // still waiting on a real match to be played
        }

        $winnerId = $match->registration1_id ?? $match->registration2_id;

        $this->settle($match, $winnerId, 'walkover');
    }

    private function settle(GameMatch $match, ?int $winnerRegistrationId, string $status): void
    {
        $match->update(['winner_registration_id' => $winnerRegistrationId, 'status' => $status]);
        $match = $match->fresh();

        if ($winnerRegistrationId && $match->next_match_id) {
            $this->propagateWinner($match);
        } elseif (! $winnerRegistrationId && $match->next_match_id) {
            // Dead match (no participants at all reached it): let the downstream
            // match re-check itself since one of its predecessors is now terminal.
            $this->maybeSettle(GameMatch::find($match->next_match_id));
        } elseif ($winnerRegistrationId && ! $match->next_match_id) {
            $this->completeBracket($match);
        }

        if ($match->loser_next_match_id) {
            $this->propagateLoserIfApplicable($match);
        }
    }

    /**
     * Called after a real result is recorded via MatchResultRecorder.
     */
    public function propagateWinner(GameMatch $match): void
    {
        if ($match->next_match_id) {
            $next = GameMatch::find($match->next_match_id);
            $field = $match->next_match_slot === 2 ? 'registration2_id' : 'registration1_id';
            $next->update([$field => $match->winner_registration_id]);
            $this->maybeSettle($next->fresh());

            return;
        }

        $this->completeBracket($match);
    }

    private function completeBracket(GameMatch $match): void
    {
        if ($match->bracket_type === 'final') {
            $match->event->update(['status' => 'completed']);
        } elseif ($match->bracket_type === 'winners' && $match->event->format === 'single_elimination') {
            $match->event->update(['status' => 'completed']);
        }
    }

    public function propagateLoserIfApplicable(GameMatch $match): void
    {
        if (! $match->loser_next_match_id) {
            return;
        }

        $next = GameMatch::find($match->loser_next_match_id);

        $loserId = null;
        if ($match->winner_registration_id) {
            $loserId = $match->registration1_id === $match->winner_registration_id
                ? $match->registration2_id
                : $match->registration1_id;
        }

        if ($loserId) {
            $field = $match->loser_next_match_slot === 2 ? 'registration2_id' : 'registration1_id';
            $next->update([$field => $loserId]);
        }

        // Notify the target even when there's no loser to send (e.g. this was
        // itself a bye) — it may now be able to resolve itself either way.
        $this->maybeSettle($next->fresh());
    }
}
