@php $e = $event ?? null; @endphp

<div>
    <x-input-label for="name" value="Event name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="e.g. Men's Singles" value="{{ old('name', $e?->name) }}" required autofocus />
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="play_type" value="Play type" />
        <select id="play_type" name="play_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (['singles' => 'Singles', 'doubles' => 'Doubles'] as $value => $label)
                <option value="{{ $value }}" @selected(old('play_type', $e?->play_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="category" value="Category" />
        <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (['men' => "Men's", 'women' => "Women's", 'mixed' => 'Mixed', 'open' => 'Open'] as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $e?->category) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-4">
    <x-input-label for="format" value="Bracket format" />
    <select id="format" name="format" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        @foreach (['single_elimination' => 'Single elimination', 'double_elimination' => 'Double elimination', 'round_robin' => 'Round robin'] as $value => $label)
            <option value="{{ $value }}" @selected(old('format', $e?->format) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if ($e && $e->matches()->exists())
        <p class="mt-1 text-sm text-amber-600">Changing the format won't rebuild an already-generated bracket &mdash; regenerate it after saving.</p>
    @endif
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="max_participants" value="Max participants" />
        <x-text-input id="max_participants" name="max_participants" type="number" min="2" class="mt-1 block w-full" value="{{ old('max_participants', $e?->max_participants) }}" />
    </div>
    <div>
        <x-input-label for="points_to_win" value="Points to win a game" />
        <x-text-input id="points_to_win" name="points_to_win" type="number" min="11" max="30" class="mt-1 block w-full" value="{{ old('points_to_win', $e?->points_to_win ?? 21) }}" required />
    </div>
    <div>
        <x-input-label for="best_of_games" value="Best of" />
        <select id="best_of_games" name="best_of_games" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach ([1, 3, 5] as $value)
                <option value="{{ $value }}" @selected((int) old('best_of_games', $e?->best_of_games ?? 3) === $value)>{{ $value }} games</option>
            @endforeach
        </select>
    </div>
</div>
