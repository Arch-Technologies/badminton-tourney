<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::where('status', '!=', 'draft')
            ->withCount('events')
            ->orderByDesc('start_date')
            ->paginate(12);

        return view('public.tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament): View
    {
        abort_if($tournament->status === 'draft', 404);

        $tournament->load(['events' => fn ($q) => $q->withCount(['registrations' => fn ($r) => $r->where('status', 'approved')])]);

        return view('public.tournaments.show', compact('tournament'));
    }
}
