<table class="min-w-full divide-y divide-gray-200 dark-mode-border">
    <thead class="bg-gray-50 dark-mode-bg-secondary">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark-mode-text uppercase">#</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark-mode-text uppercase">Ойыншы</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark-mode-text uppercase">Команда</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase">
                @if($statType === 'goals') Гол @endif
                @if($statType === 'yellow_cards') СК @endif
                @if($statType === 'red_cards') ҚК @endif
            </th>
        </tr>
    </thead>
    <tbody class="bg-white dark-mode-bg-secondary divide-y divide-gray-200 dark-mode-border">
    @forelse($stats as $stat)
        <tr>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark-mode-text">{{ $loop->iteration }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark-mode-text-heading">{{ $stat->player->full_name }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark-mode-text">{{ $stat->team->name }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-center font-bold text-gray-900 dark-mode-text-heading">{{ $stat->$statType }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark-mode-text">Деректер жоқ.</td>
        </tr>
    @endforelse
    </tbody>
</table>