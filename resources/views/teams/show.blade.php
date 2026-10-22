@extends('layouts.app')

@section('title', $team->name . ' - Команда')

@section('sidebar')
    <div class="bg-white dark-mode-bg-secondary p-4 rounded-lg shadow">
        <div class="bg-green-600 text-white font-bold text-md uppercase px-4 py-2 rounded-t-md -m-4 mb-4">Турнирлер</div>
        <ul class="space-y-1">
            @foreach($tournaments as $tournament)
                @if($tournament->seasons->isNotEmpty())
                    <li>
                        <a href="{{ route('home', ['season' => $tournament->seasons->first()->id]) }}" class="block p-2 rounded-md text-sm hover:bg-gray-100 dark-mode-hover text-gray-700 dark-mode-text">
                           {{ $tournament->name }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Team Header -->
    <div class="bg-white dark-mode-bg-secondary rounded-lg shadow p-6 flex items-center space-x-4">
        <div class="w-16 h-16 bg-gray-200 dark-mode-bg rounded-full flex items-center justify-center">
            <span class="text-2xl font-bold text-gray-500">{{ substr($team->name, 0, 1) }}</span>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark-mode-text-heading">{{ $team->name }}</h1>
            <p class="text-gray-500 dark-mode-text">{{ $team->city ?? 'Қаласы белгісіз' }}</p>
        </div>
    </div>

    <!-- --- ВОТ ГЛАВНОЕ ИЗМЕНЕНИЕ: ТАБЛИЦА С СОСТАВОМ --- -->
    <div class="bg-white dark-mode-bg-secondary rounded-lg shadow overflow-hidden">
        <div class="p-4 bg-gray-50 dark-mode-bg-secondary border-b dark-mode-border">
            <h2 class="text-lg font-bold text-gray-800 dark-mode-text-heading">Ағымдағы маусым құрамы</h2>
        </div>
        <div class="overflow-x-auto">
            @if($roster && $roster->players->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark-mode-bg-secondary text-left text-xs uppercase font-medium text-gray-500 dark-mode-text">
                        <tr>
                            <th class="px-4 py-2">№</th>
                            <th class="px-4 py-2">Ойыншы</th>
                            <th class="px-4 py-2 text-center" title="Ойындар">О</th>
                            <th class="px-4 py-2 text-center" title="Голдар">Г</th>
                            <th class="px-4 py-2 text-center" title="Сары қағаздар">СҚ</th>
                            <th class="px-4 py-2 text-center" title="Қызыл қағаздар">ҚК</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark-mode-border">
                        @foreach($roster->players->sortBy('pivot.jersey_number') as $player)
                            @php
                                $stats = $playerStats->get($player->id);
                            @endphp
                            <tr class="hover:bg-gray-50 dark-mode-hover">
                                <td class="px-4 py-2 font-bold text-gray-500 dark-mode-text">{{ $player->pivot->jersey_number }}</td>
                                <td class="px-4 py-2 font-medium text-gray-900 dark-mode-text-heading">
                                    <a href="{{ route('players.show', $player) }}" class="hover:underline">{{ $player->full_name }}</a>
                                </td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->games_played ?? 0 }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->goals ?? 0 }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->yellow_cards ?? 0 }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->red_cards ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 dark-mode-text p-4">Ағымдағы маусымға арналған құрам табылмады.</p>
            @endif
        </div>
    </div>
    <!-- --- КОНЕЦ ИЗМЕНЕНИЯ --- -->

    <!-- Recent & Upcoming Games (без изменений) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Results -->
        <div class="bg-white dark-mode-bg-secondary rounded-lg shadow">
            <div class="p-4 bg-gray-50 dark-mode-bg-secondary border-b dark-mode-border rounded-t-lg">
                <h2 class="text-lg font-bold text-gray-800 dark-mode-text-heading">Соңғы ойындар</h2>
            </div>
            <div class="p-4 space-y-2">
                @forelse($recentGames as $game)
                    @php
                        $isHome = $game->home_team_id == $team->id;
                        $opponent = $isHome ? $game->awayTeam : $game->homeTeam;
                        $score = $isHome ? $game->home_score . ':' . $game->away_score : $game->away_score . ':' . $game->home_score;
                        $result = ($game->home_score == $game->away_score) ? 'D' : (($isHome && $game->home_score > $game->away_score) || (!$isHome && $game->away_score > $game->home_score) ? 'W' : 'L');
                        $resultColor = ['W' => 'bg-green-100 text-green-800', 'D' => 'bg-gray-100 text-gray-800', 'L' => 'bg-red-100 text-red-800'];
                    @endphp
                    <div class="flex items-center justify-between text-sm p-2 rounded-md">
                        <span class="font-bold w-6 h-6 flex items-center justify-center rounded {{ $resultColor[$result] }}">{{ $result }}</span>
                        <a href="{{ route('teams.show', $opponent) }}" class="flex-1 mx-2 text-gray-600 dark-mode-text hover:underline">{{ $opponent->name }}</a>
                        <a href="{{ route('games.show', $game) }}" class="font-bold text-gray-800 dark-mode-text-heading hover:underline">{{ $score }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark-mode-text text-sm">Ойналған ойындар жоқ.</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Fixtures -->
        <div class="bg-white dark-mode-bg-secondary rounded-lg shadow">
            <div class="p-4 bg-gray-50 dark-mode-bg-secondary border-b dark-mode-border rounded-t-lg">
                <h2 class="text-lg font-bold text-gray-800 dark-mode-text-heading">Алдағы ойындар</h2>
            </div>
            <div class="p-4 space-y-2">
                @forelse($upcomingGames as $game)
                     @php
                        $opponent = $game->home_team_id == $team->id ? $game->awayTeam : $game->homeTeam;
                    @endphp
                    <a href="{{ route('games.show', $game) }}" class="block p-2 rounded-md hover:bg-gray-100 dark-mode-hover">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex-1">
                                <a href="{{ route('teams.show', $opponent) }}" class="font-medium text-gray-800 dark-mode-text-heading hover:underline">{{ $opponent->name }}</a>
                                <p class="text-xs text-gray-500 dark-mode-text">{{ $game->season->tournament->name }}</p>
                            </div>
                            <span class="text-gray-600 dark-mode-text">{{ $game->game_datetime->format('d.m.Y H:i') }}</span>
                        </div>
                    </a>
                @empty
                     <p class="text-gray-500 dark-mode-text text-sm">Жоспарланған ойындар жоқ.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('ads')
    <div class="space-y-6">
        @forelse($advertisements as $ad)
            <div class="bg-white dark-mode-bg-secondary rounded-lg shadow">
                 <div class="bg-gray-100 dark-mode-bg-secondary text-gray-500 dark-mode-text text-xs uppercase font-bold text-center py-1 rounded-t-md border-b dark-mode-border">Жарнама</div>
                 <div class="p-4">{!! $ad->code !!}</div>
            </div>
        @empty
            <div class="bg-white dark-mode-bg-secondary p-4 rounded-lg shadow text-center text-gray-400 text-sm">Жарнама орны</div>
        @endforelse
    </div>
@endsection