<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tournament;
use App\Services\BracketGenerator;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class BracketController extends Controller
{
    public function store(Tournament $tournament, Event $event, BracketGenerator $generator): RedirectResponse
    {
        try {
            $generator->generate($event);
        } catch (RuntimeException $e) {
            return back()->withErrors(['bracket' => $e->getMessage()]);
        }

        return redirect()
            ->route('organizer.tournaments.events.show', [$tournament, $event])
            ->with('status', 'Bracket generated.');
    }
}
