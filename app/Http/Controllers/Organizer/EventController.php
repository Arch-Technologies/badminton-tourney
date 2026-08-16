<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function create(Tournament $tournament): View
    {
        return view('organizer.events.create', compact('tournament'));
    }

    public function store(Request $request, Tournament $tournament): RedirectResponse
    {
        $data = $this->validated($request);
        $tournament->events()->create($data);

        return redirect()->route('organizer.tournaments.show', $tournament)
            ->with('status', 'Event added.');
    }

    public function show(Tournament $tournament, Event $event): View
    {
        $event->load(['registrations.player', 'registrations.partner']);

        return view('organizer.events.show', compact('tournament', 'event'));
    }

    public function edit(Tournament $tournament, Event $event): View
    {
        return view('organizer.events.edit', compact('tournament', 'event'));
    }

    public function update(Request $request, Tournament $tournament, Event $event): RedirectResponse
    {
        $event->update($this->validated($request));

        return redirect()->route('organizer.tournaments.events.show', [$tournament, $event])
            ->with('status', 'Event updated.');
    }

    public function destroy(Tournament $tournament, Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('organizer.tournaments.show', $tournament)
            ->with('status', 'Event deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'play_type' => 'required|in:singles,doubles',
            'category' => 'required|in:men,women,mixed,open',
            'format' => 'required|in:single_elimination,double_elimination,round_robin',
            'max_participants' => 'nullable|integer|min:2',
            'points_to_win' => 'required|integer|min:11|max:30',
            'best_of_games' => 'required|integer|in:1,3,5',
        ]);
    }
}
