<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::withCount('events')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('organizer.tournaments.index', compact('tournaments'));
    }

    public function create(): View
    {
        return view('organizer.tournaments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = $request->user()->id;

        $tournament = Tournament::create($data);

        return redirect()->route('organizer.tournaments.show', $tournament)
            ->with('status', 'Tournament created.');
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['events' => fn ($q) => $q->withCount('registrations')]);

        return view('organizer.tournaments.show', compact('tournament'));
    }

    public function edit(Tournament $tournament): View
    {
        return view('organizer.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $data = $this->validated($request, $tournament);

        if ($data['name'] !== $tournament->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $tournament->id);
        }

        $tournament->update($data);

        return redirect()->route('organizer.tournaments.show', $tournament)
            ->with('status', 'Tournament updated.');
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        $tournament->delete();

        return redirect()->route('organizer.tournaments.index')
            ->with('status', 'Tournament deleted.');
    }

    private function validated(Request $request, ?Tournament $tournament = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_opens_at' => 'nullable|date',
            'registration_closes_at' => 'nullable|date|after_or_equal:registration_opens_at',
            'status' => 'required|in:draft,registration_open,registration_closed,in_progress,completed,cancelled',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Tournament::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
