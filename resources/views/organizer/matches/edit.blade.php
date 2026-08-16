<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-500">{{ $tournament->name }} &middot; {{ $event->name }}</p>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enter Match Result</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="mb-6 grid grid-cols-2 gap-4 text-center">
                    <div class="p-4 rounded-md {{ $match->winner_registration_id === $match->registration1_id ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="font-semibold text-gray-900">{{ $match->registration1?->label() ?? 'TBD' }}</div>
                    </div>
                    <div class="p-4 rounded-md {{ $match->winner_registration_id === $match->registration2_id ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="font-semibold text-gray-900">{{ $match->registration2?->label() ?? 'TBD' }}</div>
                    </div>
                </div>

                @unless ($match->hasBothParticipants())
                    <p class="text-amber-600 text-sm">Both participants must be determined (earlier matches completed) before you can enter a result.</p>
                @else
                    <form method="POST" action="{{ route('organizer.tournaments.events.matches.update', [$tournament, $event, $match]) }}"
                          x-data="{ games: {{ Illuminate\Support\Js::from($match->games->isNotEmpty()
                                ? $match->games->map(fn ($g) => ['score1' => $g->score1, 'score2' => $g->score2])->values()
                                : [['score1' => '', 'score2' => '']]) }} }">
                        @csrf
                        @method('PUT')

                        <p class="text-sm text-gray-500 mb-3">Best of {{ $event->best_of_games }} games, first to {{ $event->points_to_win }} (win by 2, cap 30).</p>

                        <div class="space-y-3">
                            <template x-for="(game, index) in games" :key="index">
                                <div class="flex gap-3 items-center">
                                    <span class="text-sm text-gray-500 w-16" x-text="'Game ' + (index + 1)"></span>
                                    <input type="number" min="0" max="30" required
                                           :name="`games[${index}][score1]`" x-model="game.score1"
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20">
                                    <span class="text-gray-400">&ndash;</span>
                                    <input type="number" min="0" max="30" required
                                           :name="`games[${index}][score2]`" x-model="game.score2"
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20">
                                    <button type="button" x-show="games.length > 1" @click="games.splice(index, 1)" class="text-sm text-gray-400 hover:text-red-600">Remove</button>
                                </div>
                            </template>
                        </div>

                        <button type="button" x-show="games.length < {{ $event->best_of_games }}" @click="games.push({score1: '', score2: ''})"
                                class="mt-3 text-sm text-indigo-600 hover:underline">+ Add game</button>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button type="submit">Save Result</x-primary-button>
                        </div>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
