@php
    $bracketTypeLabels = [
        'winners' => $event->format === 'double_elimination' ? 'Winners Bracket' : 'Bracket',
        'losers' => 'Losers Bracket',
        'final' => 'Grand Final',
    ];
    $grouped = $matches->groupBy('bracket_type');
    $order = ['winners', 'losers', 'final'];
@endphp

@foreach ($order as $bracketType)
    @continue(! $grouped->has($bracketType))
    @php $rounds = $grouped[$bracketType]->groupBy('round')->sortKeys(); @endphp

    <div class="mb-8">
        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $bracketTypeLabels[$bracketType] }}</h4>
        <div class="flex gap-6 overflow-x-auto pb-2">
            @foreach ($rounds as $roundNumber => $roundMatches)
                <div class="flex-shrink-0 w-64 space-y-4">
                    <div class="text-xs font-medium text-gray-400">
                        Round {{ $roundNumber }}
                    </div>
                    @foreach ($roundMatches->sortBy('match_number') as $match)
                        <div class="border border-gray-200 rounded-md overflow-hidden">
                            @foreach ([1, 2] as $slot)
                                @php
                                    $reg = $slot === 1 ? $match->registration1 : $match->registration2;
                                    $isWinner = $reg && $match->winner_registration_id === $reg->id;
                                @endphp
                                <div class="px-3 py-2 text-sm flex justify-between items-center {{ $slot === 1 ? 'border-b border-gray-100' : '' }} {{ $isWinner ? 'bg-green-50 font-medium text-green-900' : 'text-gray-700' }}">
                                    <span class="truncate">{{ $reg?->label() ?? ($match->status === 'pending' ? 'TBD' : 'BYE') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="-mt-3 flex justify-between items-center text-xs text-gray-500 px-1">
                            <span>
                                @if ($match->status === 'completed' || $match->status === 'walkover')
                                    {{ $match->games->map(fn ($g) => $g->score1.'-'.$g->score2)->implode(', ') }}
                                @else
                                    <x-status-badge :status="$match->status" />
                                @endif
                            </span>
                            @if ($isOrganizer)
                                <a href="{{ route('organizer.tournaments.events.matches.edit', [$tournament, $event, $match]) }}" class="text-indigo-600 hover:underline">
                                    {{ $match->status === 'completed' ? 'Edit' : 'Enter score' }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach
