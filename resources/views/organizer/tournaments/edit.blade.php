<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Tournament</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('organizer.tournaments.update', $tournament) }}">
                    @csrf
                    @method('PUT')
                    @include('organizer.tournaments._form', ['tournament' => $tournament])

                    <div class="mt-6 flex justify-between items-center">
                        <form method="POST" action="{{ route('organizer.tournaments.destroy', $tournament) }}" onsubmit="return confirm('Delete this tournament and all its events, registrations and matches?');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">Delete Tournament</x-danger-button>
                        </form>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
