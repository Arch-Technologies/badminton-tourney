<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Event &mdash; {{ $tournament->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('organizer.tournaments.events.update', [$tournament, $event]) }}">
                    @csrf
                    @method('PUT')
                    @include('organizer.events._form', ['event' => $event])

                    <div class="mt-6 flex justify-between items-center">
                        <form method="POST" action="{{ route('organizer.tournaments.events.destroy', [$tournament, $event]) }}" onsubmit="return confirm('Delete this event and all its registrations and matches?');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">Delete Event</x-danger-button>
                        </form>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
