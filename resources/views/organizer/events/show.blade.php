@php
    $matches = $event->matches()->with([
        'registration1.player', 'registration1.partner',
        'registration2.player', 'registration2.partner',
        'winner', 'games',
    ])->orderBy('round')->orderBy('match_number')->get();
    $isOrganizer = true;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">{{ $tournament->name }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $event->name }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$event->status" />
                <a href="{{ route('organizer.tournaments.events.edit', [$tournament, $event]) }}" class="text-sm text-gray-600 hover:text-gray-900">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Registrations --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Registrations</h3>

                <form method="POST" action="{{ route('organizer.tournaments.events.registrations.store', [$tournament, $event]) }}" class="grid grid-cols-1 sm:grid-cols-{{ $event->isDoubles() ? 5 : 3 }} gap-3 mb-6 items-end">
                    @csrf
                    <div>
                        <x-input-label value="Player name" />
                        <x-text-input name="player_name" type="text" class="mt-1 block w-full" placeholder="New player name" />
                    </div>
                    @if ($event->isDoubles())
                        <div>
                            <x-input-label value="Partner name" />
                            <x-text-input name="partner_name" type="text" class="mt-1 block w-full" placeholder="Partner name" />
                        </div>
                    @endif
                    <div>
                        <x-input-label value="Seed (optional)" />
                        <x-text-input name="seed" type="number" min="1" class="mt-1 block w-full" />
                    </div>
                    <div class="sm:col-span-1">
                        <x-primary-button type="submit">Add & Approve</x-primary-button>
                    </div>
                </form>

                <div class="divide-y divide-gray-100">
                    @forelse ($event->registrations as $registration)
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <div class="font-medium text-gray-900">{{ $registration->label() }}</div>
                                @if ($registration->seed)
                                    <div class="text-xs text-gray-500">Seed #{{ $registration->seed }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$registration->status" />
                                @if ($registration->status === 'pending')
                                    <form method="POST" action="{{ route('organizer.tournaments.events.registrations.update', [$tournament, $event, $registration]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="text-sm text-green-700 hover:underline">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('organizer.tournaments.events.registrations.update', [$tournament, $event, $registration]) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="text-sm text-red-700 hover:underline">Reject</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('organizer.tournaments.events.registrations.destroy', [$tournament, $event, $registration]) }}" onsubmit="return confirm('Remove this registration?');">
                                    @csrf @method('DELETE')
                                    <button class="text-sm text-gray-400 hover:text-red-700">Remove</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm py-2">No registrations yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Bracket generation --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $event->format === 'round_robin' ? 'Schedule' : 'Bracket' }}
                    </h3>
                    <form method="POST" action="{{ route('organizer.tournaments.events.bracket.store', [$tournament, $event]) }}" onsubmit="return confirm('{{ $matches->isNotEmpty() ? 'This will erase all existing matches and results for this event and regenerate from approved registrations. Continue?' : 'Generate the bracket from approved registrations?' }}');">
                        @csrf
                        <x-secondary-button type="submit">
                            {{ $matches->isNotEmpty() ? 'Regenerate' : 'Generate' }} from {{ $event->approvedRegistrations()->count() }} approved
                        </x-secondary-button>
                    </form>
                </div>

                @if ($matches->isEmpty())
                    <p class="text-gray-500 text-sm">No bracket generated yet. Approve registrations, then generate.</p>
                @elseif ($event->format === 'round_robin')
                    @include('partials.round-robin-schedule', ['matches' => $matches, 'isOrganizer' => true])
                @else
                    @include('partials.bracket', ['matches' => $matches, 'isOrganizer' => true])
                @endif
            </div>

            @if ($event->format === 'round_robin' && $matches->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Standings</h3>
                    @include('partials.standings', ['standings' => app(\App\Services\StandingsCalculator::class)->forRoundRobin($event)])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
