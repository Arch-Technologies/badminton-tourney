<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(Tournament $tournament, Event $event): View
    {
        abort_unless($tournament->isRegistrationOpen(), 404);

        return view('public.registrations.create', compact('tournament', 'event'));
    }

    public function store(Request $request, Tournament $tournament, Event $event): RedirectResponse
    {
        abort_unless($tournament->isRegistrationOpen(), 404);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ];

        if ($event->isDoubles()) {
            $rules['partner_name'] = 'required|string|max:255';
            $rules['partner_email'] = 'nullable|email|max:255';
        }

        $data = $request->validate($rules);

        $player = $this->findOrCreatePlayer($data['name'], $data['email'], $data['phone'] ?? null);

        $partnerId = null;
        if ($event->isDoubles()) {
            $partnerId = $this->findOrCreatePlayer($data['partner_name'], $data['partner_email'] ?? null)->id;
        }

        $existing = $event->registrations()->where('player_id', $player->id)->first();
        if ($existing) {
            return back()->withErrors(['name' => 'This player is already registered for this event.']);
        }

        $event->registrations()->create([
            'player_id' => $player->id,
            'partner_player_id' => $partnerId,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('tournaments.show', $tournament)
            ->with('status', 'Registration submitted! The organizer will confirm it shortly.');
    }

    private function findOrCreatePlayer(string $name, ?string $email, ?string $phone = null): Player
    {
        if ($email) {
            $existing = Player::where('email', $email)->first();
            if ($existing) {
                return $existing;
            }
        }

        return Player::create(['name' => $name, 'email' => $email, 'phone' => $phone]);
    }
}
