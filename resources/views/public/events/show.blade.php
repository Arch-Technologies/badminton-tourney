@php $isOrganizer = false; @endphp
<x-public-layout :title="$event->name.' - '.$tournament->name">
    <div class="mb-6">
        <a href="{{ route('tournaments.show', $tournament) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $tournament->name }}</a>
        <div class="flex justify-between items-start mt-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $event->name }}</h1>
            <x-status-badge :status="$event->status" />
        </div>
        <p class="text-gray-500 mt-1">
            {{ ucfirst($event->play_type) }} &middot; {{ str_replace('_', ' ', ucfirst($event->format)) }} &middot;
            Best of {{ $event->best_of_games }} to {{ $event->points_to_win }}
        </p>
        @if ($tournament->isRegistrationOpen())
            <a href="{{ route('tournaments.events.register.create', [$tournament, $event]) }}"
               class="inline-block mt-3 text-sm px-3 py-1.5 bg-gray-800 text-white rounded-md hover:bg-gray-700">Register for this event</a>
        @endif
    </div>

    @if ($event->matches->isEmpty())
        <p class="text-gray-500">The {{ $event->format === 'round_robin' ? 'schedule' : 'bracket' }} hasn't been generated yet.</p>
    @else
        @if ($event->format === 'round_robin')
            @if ($standings)
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Standings</h2>
                <div class="mb-8">@include('partials.standings', ['standings' => $standings])</div>
            @endif
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Schedule & Results</h2>
            @include('partials.round-robin-schedule', ['matches' => $event->matches, 'isOrganizer' => false])
        @else
            @include('partials.bracket', ['matches' => $event->matches, 'isOrganizer' => false])
        @endif
    @endif
</x-public-layout>
