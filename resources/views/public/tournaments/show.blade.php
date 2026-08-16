<x-public-layout :title="$tournament->name">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <h1 class="text-2xl font-bold text-gray-900">{{ $tournament->name }}</h1>
            <x-status-badge :status="$tournament->status" />
        </div>
        <p class="text-gray-500 mt-1">
            {{ $tournament->start_date->format('M j, Y') }} &ndash; {{ $tournament->end_date->format('M j, Y') }}
            @if ($tournament->venue) &middot; {{ $tournament->venue }}@if($tournament->city), {{ $tournament->city }}@endif @endif
        </p>
        @if ($tournament->description)
            <p class="text-gray-700 mt-4 whitespace-pre-line">{{ $tournament->description }}</p>
        @endif
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-3">Events</h2>
    <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-100">
        @forelse ($tournament->events as $event)
            <div class="p-4 flex justify-between items-center">
                <div>
                    <a href="{{ route('tournaments.events.show', [$tournament, $event]) }}" class="font-medium text-gray-900 hover:underline">{{ $event->name }}</a>
                    <div class="text-sm text-gray-500">
                        {{ ucfirst($event->play_type) }} &middot; {{ str_replace('_', ' ', ucfirst($event->format)) }} &middot;
                        {{ $event->registrations_count }} registered
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-status-badge :status="$event->status" />
                    @if ($tournament->isRegistrationOpen())
                        <a href="{{ route('tournaments.events.register.create', [$tournament, $event]) }}"
                           class="text-sm px-3 py-1.5 bg-gray-800 text-white rounded-md hover:bg-gray-700">Register</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-4 text-gray-500">No events published yet.</p>
        @endforelse
    </div>
</x-public-layout>
