<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Player</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('organizer.players.update', $player) }}">
                    @csrf
                    @method('PUT')
                    @include('organizer.players._form', ['player' => $player])
                    <div class="mt-6 flex justify-between items-center">
                        <form method="POST" action="{{ route('organizer.players.destroy', $player) }}" onsubmit="return confirm('Remove this player?');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">Delete Player</x-danger-button>
                        </form>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
