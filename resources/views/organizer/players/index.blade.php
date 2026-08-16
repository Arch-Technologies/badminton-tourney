<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Players</h2>
            <a href="{{ route('organizer.players.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                + New Player
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="max-w-sm">
                <x-text-input name="q" type="text" class="block w-full" placeholder="Search by name or email" value="{{ request('q') }}" />
            </form>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($players as $player)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $player->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $player->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $player->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $player->city }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('organizer.players.edit', $player) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No players found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $players->links() }}</div>
        </div>
    </div>
</x-app-layout>
