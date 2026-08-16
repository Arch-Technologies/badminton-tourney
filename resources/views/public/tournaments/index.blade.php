<x-public-layout title="Tournaments">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Tournaments</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($tournaments as $tournament)
            <a href="{{ route('tournaments.show', $tournament) }}" class="block bg-white rounded-lg shadow-sm hover:shadow-md transition p-5">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="font-semibold text-gray-900">{{ $tournament->name }}</h2>
                    <x-status-badge :status="$tournament->status" />
                </div>
                <p class="text-sm text-gray-500">
                    {{ $tournament->start_date->format('M j, Y') }} &ndash; {{ $tournament->end_date->format('M j, Y') }}
                </p>
                @if ($tournament->venue)
                    <p class="text-sm text-gray-500">{{ $tournament->venue }}@if($tournament->city), {{ $tournament->city }}@endif</p>
                @endif
                <p class="text-sm text-gray-400 mt-2">{{ $tournament->events_count }} {{ Str::plural('event', $tournament->events_count) }}</p>
            </a>
        @empty
            <p class="text-gray-500 col-span-full">No tournaments have been published yet. Check back soon.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $tournaments->links() }}</div>
</x-public-layout>
