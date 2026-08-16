<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $tournament->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $tournament->start_date->format('M j, Y') }} &ndash; {{ $tournament->end_date->format('M j, Y') }}
                    @if ($tournament->venue) &middot; {{ $tournament->venue }} @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$tournament->status" />
                <a href="{{ route('organizer.tournaments.edit', $tournament) }}" class="text-sm text-gray-600 hover:text-gray-900">Edit</a>
                <a href="{{ route('tournaments.show', $tournament) }}" class="text-sm text-gray-600 hover:text-gray-900" target="_blank">View public page &rarr;</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Events</h3>
                    <a href="{{ route('organizer.tournaments.events.create', $tournament) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        + Add Event
                    </a>
                </div>

                @forelse ($tournament->events as $event)
                    <a href="{{ route('organizer.tournaments.events.show', [$tournament, $event]) }}"
                       class="flex justify-between items-center py-3 px-3 -mx-3 rounded-md hover:bg-gray-50 border-b last:border-b-0 border-gray-100">
                        <div>
                            <div class="font-medium text-gray-900">{{ $event->name }}</div>
                            <div class="text-sm text-gray-500">
                                {{ ucfirst($event->play_type) }} &middot; {{ ucfirst($event->category) }} &middot;
                                {{ str_replace('_', ' ', ucfirst($event->format)) }} &middot;
                                {{ $event->registrations_count }} registered
                            </div>
                        </div>
                        <x-status-badge :status="$event->status" />
                    </a>
                @empty
                    <p class="text-gray-500 text-sm">No events yet. Add Men's Singles, Women's Doubles, Mixed Doubles, etc.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
