@extends('layouts.app')

@section('title', $game->homeTeam->name . ' - ' . $game->awayTeam->name)

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
<div class="bg-white dark-mode-bg-secondary rounded-lg shadow">
    <!-- Match Header -->
    <div class="p-4 text-center border-b dark-mode-border">
        <div class="text-sm text-gray-500 dark-mode-text">
            {{ $game->season->tournament->name }} - {{ $game->season->name }}
        </div>
        <div class="flex items-center justify-center my-4">
            <a href="{{ route('teams.show', $game->homeTeam) }}" class="text-center w-1/3 hover:underline">
                <h2 class="font-bold text-xl dark-mode-text-heading">{{ $game->homeTeam->name }}</h2>
            </a>
            <div class="text-4xl font-bold px-4 dark-mode-text-heading">
                {{ $game->home_score ?? '-' }} : {{ $game->away_score ?? '-' }}
            </div>
            <a href="{{ route('teams.show', $game->awayTeam) }}" class="text-center w-1/3 hover:underline">
                <h2 class="font-bold text-xl dark-mode-text-heading">{{ $game->awayTeam->name }}</h2>
            </a>
        </div>
        <div class="text-sm text-gray-500 dark-mode-text">
            {{ $game->game_datetime->format('d F Y, H:i') }}
        </div>
    </div>

    <!-- Match Content Tabs -->
    <div x-data="{ tab: 'summary' }">
        <div class="border-b border-gray-200 dark-mode-border">
            <nav class="-mb-px flex space-x-4 px-4" aria-label="Tabs">
                <button @click="tab = 'summary'" :class="{ 'border-green-500 text-green-600': tab === 'summary', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'summary' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Шолу</button>
                <button @click="tab = 'lineups'" :class="{ 'border-green-500 text-green-600': tab === 'lineups', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'lineups' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Құрамдар</button>
            </nav>
        </div>

        <div class="p-4">
            <!-- Summary Tab (Events Timeline) -->
            <div x-show="tab === 'summary'" class="space-y-3">
                @forelse($game->events as $event)
                    <div class="flex items-center gap-4 text-sm">
                        <div class="w-12 text-center font-bold text-gray-700 dark-mode-text">{{ $event->minute }}'</div>
                        <div class="flex-1 flex items-center gap-2">
                            @if($event->type == 'goal') <span class="text-lg">⚽️</span> @endif
                            @if($event->type == 'yellow_card') <span class="inline-block w-3 h-4 bg-yellow-400 rounded-sm"></span> @endif
                            @if($event->type == 'second_yellow_card') <span class="inline-block w-3 h-4 bg-yellow-400 rounded-sm"></span> + <span class="inline-block w-3 h-4 bg-red-500 rounded-sm"></span> @endif
                            @if($event->type == 'red_card') <span class="inline-block w-3 h-4 bg-red-500 rounded-sm"></span> @endif
                            
                            <div>
                                <a href="{{ route('players.show', $event->player) }}" class="font-semibold hover:underline dark-mode-text-heading">{{ $event->player->full_name }}</a>
                                <span class="text-gray-500 dark-mode-text">({{ $event->team->name }})</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark-mode-text text-center">Бұл матчта оқиғалар тіркелмеген.</p>
                @endforelse
            </div>

            <!-- Lineups Tab -->
            <div x-show="tab === 'lineups'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Home Team Lineup -->
                <div>
                    <h3 class="font-bold text-lg mb-2 dark-mode-text-heading">{{ $game->homeTeam->name }}</h3>
                    <div class="space-y-1">
                        @foreach($homeLineup->where('status', 'starter')->sortBy('player.pivot.jersey_number') as $lineup)
                            <a href="{{ route('players.show', $lineup->player) }}" class="flex items-center gap-2 text-sm p-1 rounded hover:bg-gray-100 dark-mode-hover">
                                <span class="w-6 font-bold text-gray-500 dark-mode-text">{{ $lineup->player->pivot->jersey_number ?? '' }}</span>
                                <span class="dark-mode-text-heading">{{ $lineup->player->full_name }}</span>
                            </a>
                        @endforeach
                    </div>
                    <h4 class="font-semibold text-md mt-4 mb-2 dark-mode-text">Қосалқы құрам</h4>
                    <div class="space-y-1">
                         @foreach($homeLineup->where('status', 'substitute')->sortBy('player.pivot.jersey_number') as $lineup)
                            <a href="{{ route('players.show', $lineup->player) }}" class="flex items-center gap-2 text-sm p-1 rounded hover:bg-gray-100 dark-mode-hover">
                                <span class="w-6 font-bold text-gray-500 dark-mode-text">{{ $lineup->player->pivot->jersey_number ?? '' }}</span>
                                <span class="dark-mode-text-heading">{{ $lineup->player->full_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <!-- Away Team Lineup -->
                <div>
                    <h3 class="font-bold text-lg mb-2 dark-mode-text-heading">{{ $game->awayTeam->name }}</h3>
                    <div class="space-y-1">
                        @foreach($awayLineup->where('status', 'starter')->sortBy('player.pivot.jersey_number') as $lineup)
                             <a href="{{ route('players.show', $lineup->player) }}" class="flex items-center gap-2 text-sm p-1 rounded hover:bg-gray-100 dark-mode-hover">
                                <span class="w-6 font-bold text-gray-500 dark-mode-text">{{ $lineup->player->pivot->jersey_number ?? '' }}</span>
                                <span class="dark-mode-text-heading">{{ $lineup->player->full_name }}</span>
                            </a>
                        @endforeach
                    </div>
                    <h4 class="font-semibold text-md mt-4 mb-2 dark-mode-text">Қосалқы құрам</h4>
                    <div class="space-y-1">
                        @foreach($awayLineup->where('status', 'substitute')->sortBy('player.pivot.jersey_number') as $lineup)
                            <a href="{{ route('players.show', $lineup->player) }}" class="flex items-center gap-2 text-sm p-1 rounded hover:bg-gray-100 dark-mode-hover">
                                <span class="w-6 font-bold text-gray-500 dark-mode-text">{{ $lineup->player->pivot->jersey_number ?? '' }}</span>
                                <span class="dark-mode-text-heading">{{ $lineup->player->full_name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
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