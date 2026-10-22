@extends('layouts.app')

@section('title', 'Басты бет - Sport Platformasy')

@section('sidebar')
    <div class="bg-white dark-mode-bg-secondary p-4 rounded-lg shadow">
        <div class="bg-green-600 text-white font-bold text-md uppercase px-4 py-2 rounded-t-md -m-4 mb-4">Турнирлер</div>
        <!-- --- ВОТ ГЛАВНОЕ ИЗМЕНЕНИЕ --- -->
        <ul class="space-y-1">
            @foreach($tournaments as $tournament)
                @if($tournament->seasons->isNotEmpty())
                    <li>
                        <a href="{{ route('home', ['season' => $tournament->seasons->first()->id]) }}" 
                           class="block p-2 rounded-md text-sm hover:bg-gray-100 dark-mode-hover 
                                  {{ isset($selectedSeason) && $selectedSeason->tournament_id == $tournament->id ? 'font-bold text-green-600 bg-green-50 dark-mode-bg' : 'text-gray-700 dark-mode-text' }}">
                           {{ $tournament->name }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
        <!-- --- КОНЕЦ ИЗМЕНЕНИЯ --- -->
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- News Slider -->
    <div class="swiper news-slider h-64 rounded-lg shadow overflow-hidden">
        <div class="swiper-wrapper">
            @foreach($sliderNews as $newsItem)
            <div class="swiper-slide relative bg-gray-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="font-bold text-white text-xl mb-2 leading-tight">{{ $newsItem->title }}</h3>
                    <a href="{{ route('news.show', $newsItem) }}" class="text-green-300 hover:underline font-semibold text-sm">Толығырақ...</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="swiper-button-next text-white drop-shadow-md"></div>
        <div class="swiper-button-prev text-white drop-shadow-md"></div>
    </div>

    <!-- Main Content Tabs -->
    @if($selectedSeason)
    <div x-data="{ tab: 'matches' }" class="bg-white dark-mode-bg-secondary rounded-lg shadow">
        <div class="px-4 pt-4 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
            <h2 class="text-xl font-bold text-gray-800 dark-mode-text-heading">{{ $selectedSeason->tournament->name }}</h2>
            <form action="{{ route('home') }}" method="GET">
                <select name="season" onchange="this.form.submit()" class="text-sm form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md dark-mode-bg dark-mode-text dark-mode-border">
                    @foreach($seasonsForDropdown as $season)
                        <option value="{{ $season->id }}" {{ $selectedSeason->id == $season->id ? 'selected' : '' }}>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        
        <div class="border-b border-gray-200 dark-mode-border mt-3">
            <nav class="-mb-px flex space-x-4 px-4 overflow-x-auto" aria-label="Tabs">
                <button @click="tab = 'matches'" :class="{ 'border-green-500 text-green-600': tab === 'matches', 'border-transparent text-gray-500 dark-mode-text hover:text-gray-700 hover:border-gray-300': tab !== 'matches' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Ойындар</button>
                <button @click="tab = 'standings'" :class="{ 'border-green-500 text-green-600': tab === 'standings', 'border-transparent text-gray-500 dark-mode-text hover:text-gray-700 hover:border-gray-300': tab !== 'standings' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Кесте</button>
                <button @click="tab = 'scorers'" :class="{ 'border-green-500 text-green-600': tab === 'scorers', 'border-transparent text-gray-500 dark-mode-text hover:text-gray-700 hover:border-gray-300': tab !== 'scorers' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Сұрмергендер</button>
                <button @click="tab = 'yellow_cards'" :class="{ 'border-green-500 text-green-600': tab === 'yellow_cards', 'border-transparent text-gray-500 dark-mode-text hover:text-gray-700 hover:border-gray-300': tab !== 'yellow_cards' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Сары қағаздар</button>
                <button @click="tab = 'red_cards'" :class="{ 'border-green-500 text-green-600': tab === 'red_cards', 'border-transparent text-gray-500 dark-mode-text hover:text-gray-700 hover:border-gray-300': tab !== 'red_cards' }" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Қызыл қағаздар</button>
            </nav>
        </div>
        <div class="p-4">
            <!-- Matches Tab -->
            <div x-show="tab === 'matches'" x-data="{ subTab: 'scheduled' }">
                <div class="flex space-x-2 mb-4 border-b border-gray-200 dark-mode-border">
                    <button @click="subTab = 'scheduled'" :class="{ 'bg-green-600 text-white': subTab === 'scheduled', 'bg-gray-200 dark-mode-bg text-gray-700 dark-mode-text': subTab !== 'scheduled' }" class="px-3 py-1 text-sm font-medium rounded-t-md">Жоспарланған</button>
                    <button @click="subTab = 'finished'" :class="{ 'bg-green-600 text-white': subTab === 'finished', 'bg-gray-200 dark-mode-bg text-gray-700 dark-mode-text': subTab !== 'finished' }" class="px-3 py-1 text-sm font-medium rounded-t-md">Аяқталған</button>
                </div>

                <div x-show="subTab === 'scheduled'" class="space-y-4">
                    @forelse($gamesByStatusAndRound['scheduled'] ?? [] as $roundName => $games)
                        @include('partials.games_by_round', ['roundName' => $roundName, 'games' => $games])
                    @empty
                        <p class="text-gray-600 dark-mode-text">Жоспарланған ойындар жоқ.</p>
                    @endforelse
                </div>

                <div x-show="subTab === 'finished'" class="space-y-4">
                    @forelse($gamesByStatusAndRound['finished'] ?? [] as $roundName => $games)
                        @include('partials.games_by_round', ['roundName' => $roundName, 'games' => $games])
                    @empty
                        <p class="text-gray-600 dark-mode-text">Аяқталған ойындар жоқ.</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Standings Tab -->
            <div x-show="tab === 'standings'">@include('partials.standings_table', ['standings' => $standings])</div>
            
            <!-- Stats Tabs -->
            <div x-show="tab === 'scorers'">@include('partials.stats_table', ['stats' => $statsLeaders['top_scorers'], 'statType' => 'goals'])</div>
            <div x-show="tab === 'yellow_cards'">@include('partials.stats_table', ['stats' => $statsLeaders['top_yellow_cards'], 'statType' => 'yellow_cards'])</div>
            <div x-show="tab === 'red_cards'">@include('partials.stats_table', ['stats' => $statsLeaders['top_red_cards'], 'statType' => 'red_cards'])</div>
        </div>
    </div>
    @else
    <div class="bg-white dark-mode-bg-secondary p-6 rounded-lg shadow text-center">
        <p class="text-gray-600 dark-mode-text">Көрсетуге ағымдағы маусым табылмады.</p>
    </div>
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const swiper = new Swiper('.news-slider', {
            loop: true,
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            autoplay: { delay: 5000 },
        });
    });
</script>
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