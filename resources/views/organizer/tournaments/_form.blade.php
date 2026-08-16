@php $t = $tournament ?? null; @endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $t?->name) }}" required autofocus />
</div>

<div class="mt-4">
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $t?->description) }}</textarea>
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="venue" value="Venue" />
        <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" value="{{ old('venue', $t?->venue) }}" />
    </div>
    <div>
        <x-input-label for="city" value="City" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" value="{{ old('city', $t?->city) }}" />
    </div>
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="start_date" value="Start date" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{ old('start_date', $t?->start_date?->format('Y-m-d')) }}" required />
    </div>
    <div>
        <x-input-label for="end_date" value="End date" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{ old('end_date', $t?->end_date?->format('Y-m-d')) }}" required />
    </div>
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="registration_opens_at" value="Registration opens" />
        <x-text-input id="registration_opens_at" name="registration_opens_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('registration_opens_at', $t?->registration_opens_at?->format('Y-m-d\TH:i')) }}" />
    </div>
    <div>
        <x-input-label for="registration_closes_at" value="Registration closes" />
        <x-text-input id="registration_closes_at" name="registration_closes_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('registration_closes_at', $t?->registration_closes_at?->format('Y-m-d\TH:i')) }}" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="status" value="Status" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        @foreach (['draft', 'registration_open', 'registration_closed', 'in_progress', 'completed', 'cancelled'] as $status)
            <option value="{{ $status }}" @selected(old('status', $t?->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
</div>
