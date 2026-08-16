<x-public-layout :title="'Register - '.$event->name">
    <div class="max-w-lg mx-auto">
        <a href="{{ route('tournaments.show', $tournament) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $tournament->name }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1 mb-1">Register &mdash; {{ $event->name }}</h1>
        <p class="text-gray-500 mb-6">{{ ucfirst($event->play_type) }} &middot; {{ ucfirst($event->category) }}</p>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form method="POST" action="{{ route('tournaments.events.register.store', [$tournament, $event]) }}">
                @csrf

                <div>
                    <x-input-label for="name" :value="$event->isDoubles() ? 'Your name' : 'Name'" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus />
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" required />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" value="{{ old('phone') }}" />
                    </div>
                </div>

                @if ($event->isDoubles())
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-sm font-medium text-gray-700 mb-3">Partner</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="partner_name" value="Partner name" />
                                <x-text-input id="partner_name" name="partner_name" type="text" class="mt-1 block w-full" value="{{ old('partner_name') }}" required />
                            </div>
                            <div>
                                <x-input-label for="partner_email" value="Partner email" />
                                <x-text-input id="partner_email" name="partner_email" type="email" class="mt-1 block w-full" value="{{ old('partner_email') }}" />
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <x-primary-button type="submit">Submit Registration</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-public-layout>
