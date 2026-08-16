@props(['status'])

@php
    $colors = [
        'draft' => 'bg-gray-100 text-gray-700',
        'registration_open' => 'bg-green-100 text-green-700',
        'registration_closed' => 'bg-yellow-100 text-yellow-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'completed' => 'bg-purple-100 text-purple-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'withdrawn' => 'bg-gray-100 text-gray-500',
        'ready' => 'bg-blue-100 text-blue-700',
        'walkover' => 'bg-purple-100 text-purple-700',
        'seeded' => 'bg-blue-100 text-blue-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex px-2 py-1 text-xs font-semibold rounded-full '.($colors[$status] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
