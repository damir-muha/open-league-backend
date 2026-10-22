<div>
    <h4 class="font-bold text-gray-700 dark-mode-text-heading mb-2 text-sm uppercase">{{ $roundName ?: 'Тур белгісіз' }}</h4>
    <div class="space-y-1">
    @foreach($games as $game)
        <a href="{{ route('games.show', $game) }}" class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100 dark-mode-hover text-sm">
            <span class="text-gray-500 dark-mode-text">{{ $game->game_datetime->format('d.m H:i') }}</span>
            <span class="text-right flex-1 mx-2 text-gray-800 dark-mode-text-heading font-medium">{{ $game->homeTeam->name }}</span>
            <span class="font-bold bg-gray-200 dark-mode-bg-secondary px-2 py-1 rounded">{{ $game->home_score ?? '-' }}:{{ $game->away_score ?? '-' }}</span>
            <span class="text-left flex-1 mx-2 text-gray-800 dark-mode-text-heading font-medium">{{ $game->awayTeam->name }}</span>
        </a>
    @endforeach
    </div>
</div>