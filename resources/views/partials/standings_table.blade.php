<table class="min-w-full divide-y divide-gray-200 dark-mode-border text-sm">
    <thead class="bg-gray-50 dark-mode-bg-secondary">
        <tr>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark-mode-text uppercase">#</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark-mode-text uppercase">Команда</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Ойындар">О</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Жеңістер">Ж</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Тең ойындар">Т</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Жеңілістер">Ж-с</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Доп айырмасы">ДА</th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark-mode-text uppercase" title="Ұпайлар">Ұ</th>
        </tr>
    </thead>
    <tbody class="bg-white dark-mode-bg-secondary divide-y divide-gray-200 dark-mode-border">
    @forelse($standings as $standing)
        <tr>
            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark-mode-text">{{ $loop->iteration }}</td>
            <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900 dark-mode-text-heading">
                <a href="{{ route('teams.show', $standing->team) }}" class="hover:underline">
                    {{ $standing->team->name }}
                </a>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark-mode-text">{{ $standing->played }}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark-mode-text">{{ $standing->wins }}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark-mode-text">{{ $standing->draws }}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark-mode-text">{{ $standing->losses }}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 dark-mode-text">{{ $standing->goal_difference }}</td>
            <td class="px-3 py-2 whitespace-nowrap text-center font-bold text-gray-900 dark-mode-text-heading">{{ $standing->final_points }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-4 py-4 text-center text-sm text-gray-500 dark-mode-text">Деректер жоқ.</td>
        </tr>
    @endforelse
    </tbody>
</table>