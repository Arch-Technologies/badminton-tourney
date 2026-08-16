<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Tournament</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('organizer.tournaments.store') }}">
                    @csrf
                    @include('organizer.tournaments._form', ['tournament' => null])

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>Create Tournament</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
