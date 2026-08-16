<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tournament;
use App\Services\StandingsCalculator;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Tournament $tournament, Event $event, StandingsCalculator $standingsCalculator): View
    {
        abort_if($tournament->status === 'draft', 404);

        $event->load(['matches' => function ($q) {
            $q->with(['registration1.player', 'registration1.partner', 'registration2.player', 'registration2.partner', 'winner', 'games'])
                ->orderBy('round')->orderBy('match_number');
        }]);

        $standings = $event->format === 'round_robin' ? $standingsCalculator->forRoundRobin($event) : null;

        return view('public.events.show', compact('tournament', 'event', 'standings'));
    }
}
