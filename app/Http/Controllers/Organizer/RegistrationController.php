<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Player;
use App\Models\Registration;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function store(Request $request, Tournament $tournament, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'player_id' => 'required_without:player_name|nullable|exists:players,id',
            'player_name' => 'required_without:player_id|nullable|string|max:255',
            'partner_id' => 'nullable|exists:players,id',
            'partner_name' => 'nullable|string|max:255',
            'seed' => 'nullable|integer|min:1',
        ]);

        $player = $data['player_id'] ?? null
            ? Player::findOrFail($data['player_id'])
            : Player::create(['name' => $data['player_name']]);

        $partnerId = $data['partner_id'] ?? null;
        if (! $partnerId && ! empty($data['partner_name'])) {
            $partnerId = Player::create(['name' => $data['partner_name']])->id;
        }

        $event->registrations()->create([
            'player_id' => $player->id,
            'partner_player_id' => $partnerId,
            'status' => 'approved',
            'seed' => $data['seed'] ?? null,
        ]);

        return back()->with('status', 'Player registered.');
    }

    public function update(Request $request, Tournament $tournament, Event $event, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'withdrawn'])],
            'seed' => 'nullable|integer|min:1',
        ]);

        $registration->update($data);

        return back()->with('status', 'Registration updated.');
    }

    public function destroy(Tournament $tournament, Event $event, Registration $registration): RedirectResponse
    {
        $registration->delete();

        return back()->with('status', 'Registration removed.');
    }
}
