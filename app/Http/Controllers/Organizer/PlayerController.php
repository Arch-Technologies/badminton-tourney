<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $players = Player::query()
            ->when($request->string('q')->trim()->isNotEmpty(), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('organizer.players.index', compact('players'));
    }

    public function create(): View
    {
        return view('organizer.players.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Player::create($this->validated($request));

        return redirect()->route('organizer.players.index')->with('status', 'Player added.');
    }

    public function edit(Player $player): View
    {
        return view('organizer.players.edit', compact('player'));
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $player->update($this->validated($request));

        return redirect()->route('organizer.players.index')->with('status', 'Player updated.');
    }

    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();

        return redirect()->route('organizer.players.index')->with('status', 'Player removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'skill_level' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
    }
}
