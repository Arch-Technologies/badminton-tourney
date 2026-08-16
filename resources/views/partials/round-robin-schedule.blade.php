@php $rounds = $matches->groupBy('round')->sortKeys(); @endphp

<div class="space-y-6">
    @foreach ($rounds as $roundNumber => $roundMatches)
        <div>
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Round {{ $roundNumber }}</h4>
            <div class="bg-white shadow-sm rounded-lg divide-y divide-gray-100">
                @foreach ($roundMatches->sortBy('match_number') as $match)
                    <div class="px-4 py-3 flex justify-between items-center text-sm">
                        <div class="flex-1">
                            <span class="{{ $match->winner_registration_id === $match->registration1_id ? 'font-semibold text-green-900' : 'text-gray-700' }}">
                                {{ $match->registration1?->label() }}
                            </span>
                            <span class="text-gray-400 mx-1">vs</span>
                            <span class="{{ $match->winner_registration_id === $match->registration2_id ? 'font-semibold text-green-900' : 'text-gray-700' }}">
                                {{ $match->registration2?->label() }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($match->status === 'completed' || $match->status === 'walkover')
                                <span class="text-gray-500">{{ $match->games->map(fn ($g) => $g->score1.'-'.$g->score2)->implode(', ') }}</span>
                            @else
                                <x-status-badge :status="$match->status" />
                            @endif
                            @if ($isOrganizer ?? false)
                                <a href="{{ route('organizer.tournaments.events.matches.edit', [$tournament, $event, $match]) }}" class="text-indigo-600 hover:underline">
                                    {{ $match->status === 'completed' ? 'Edit' : 'Enter score' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
