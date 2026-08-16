<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Player</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Played</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">W</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">L</th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Point diff</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($standings as $i => $row)
                <tr>
                    <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 font-medium text-gray-900">{{ $row['registration']->label() }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ $row['played'] }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ $row['wins'] }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ $row['losses'] }}</td>
                    <td class="px-4 py-2 text-center text-gray-500">{{ $row['games_won'] - $row['games_lost'] >= 0 ? '+' : '' }}{{ $row['games_won'] - $row['games_lost'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No matches played yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
