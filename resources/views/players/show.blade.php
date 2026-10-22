@extends('layouts.app')

@section('title', $player->full_name . ' - Ойыншы')

@section('sidebar')
    {{-- Используем тот же сайдбар, что и на других страницах --}}
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
    <!-- Player Header -->
    <div class="bg-white dark-mode-bg-secondary rounded-lg shadow p-6 flex items-center space-x-4">
        {{-- В будущем здесь может быть фото игрока --}}
        <div class="w-16 h-16 bg-gray-200 dark-mode-bg rounded-full flex items-center justify-center">
            <span class="text-2xl font-bold text-gray-500">{{ substr($player->first_name, 0, 1) }}{{ substr($player->last_name, 0, 1) }}</span>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark-mode-text-heading">{{ $player->full_name }}</h1>
            <p class="text-gray-500 dark-mode-text">Позициясы: {{ $player->position ?? 'Белгісіз' }}</p>
        </div>
    </div>

    <!-- Player Stats by Season -->
    <div class="bg-white dark-mode-bg-secondary rounded-lg shadow overflow-hidden">
        <div class="p-4 bg-gray-50 dark-mode-bg-secondary border-b dark-mode-border">
            <h2 class="text-lg font-bold text-gray-800 dark-mode-text-heading">Маусымдар бойынша статистика</h2>
        </div>
        <div class="overflow-x-auto">
            @if($statsBySeason->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark-mode-bg-secondary text-left text-xs uppercase font-medium text-gray-500 dark-mode-text">
                        <tr>
                            <th class="px-4 py-2">Маусым</th>
                            <th class="px-4 py-2">Команда</th>
                            <th class="px-4 py-2 text-center" title="Ойындар">О</th>
                            <th class="px-4 py-2 text-center" title="Голдар">Г</th>
                            <th class="px-4 py-2 text-center" title="Сары қағаздар">СҚ</th>
                            <th class="px-4 py-2 text-center" title="Қызыл қағаздар">ҚК</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark-mode-border">
                        @foreach($statsBySeason as $stats)
                            <tr class="hover:bg-gray-50 dark-mode-hover">
                                <td class="px-4 py-2 font-medium text-gray-900 dark-mode-text-heading">
                                    {{ $stats->season->name }}
                                </td>
                                <td class="px-4 py-2 text-gray-600 dark-mode-text">
                                    <a href="{{ route('teams.show', $stats->team) }}" class="hover:underline">
                                        {{ $stats->team->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->games_played }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->goals }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->yellow_cards }}</td>
                                <td class="px-4 py-2 text-center text-gray-600 dark-mode-text">{{ $stats->red_cards }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 dark-mode-text p-4">Бұл ойыншы үшін статистика әлі жинақталмаған.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('ads')
    {{-- Используем тот же блок рекламы --}}
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