<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Services\MatchResultRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MatchController extends Controller
{
    public function edit(Tournament $tournament, Event $event, GameMatch $match): \Illuminate\View\View
    {
        $match->load(['registration1.player', 'registration1.partner', 'registration2.player', 'registration2.partner', 'games']);

        return view('organizer.matches.edit', compact('tournament', 'event', 'match'));
    }

    public function update(Request $request, Tournament $tournament, Event $event, GameMatch $match, MatchResultRecorder $recorder): RedirectResponse
    {
        $data = $request->validate([
            'games' => 'required|array|min:1',
            'games.*.score1' => 'required|integer|min:0|max:30',
            'games.*.score2' => 'required|integer|min:0|max:30',
        ]);

        try {
            $recorder->record($match, $data['games']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $this->maybeCompleteRoundRobin($event);

        return redirect()
            ->route('organizer.tournaments.events.show', [$tournament, $event])
            ->with('status', 'Result recorded.');
    }

    public function updateSchedule(Request $request, Tournament $tournament, Event $event, GameMatch $match): RedirectResponse
    {
        $data = $request->validate([
            'court' => 'nullable|string|max:100',
            'scheduled_at' => 'nullable|date',
        ]);

        $match->update($data);

        return back()->with('status', 'Match schedule updated.');
    }

    private function maybeCompleteRoundRobin(Event $event): void
    {
        if ($event->format !== 'round_robin') {
            return;
        }

        $incomplete = $event->matches()->whereNotIn('status', ['completed', 'walkover'])->exists();

        if (! $incomplete) {
            $event->update(['status' => 'completed']);
        }
    }
}
