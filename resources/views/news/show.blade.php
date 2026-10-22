@extends('layouts.app')

@section('title', $news->title)

@section('sidebar')
    <div class="bg-white dark-mode-bg-secondary p-4 rounded-lg shadow">
        <div class="bg-green-600 text-white font-bold text-md uppercase px-4 py-2 rounded-t-md -m-4 mb-4">Турнирлер</div>
        <ul class="space-y-1">
            @foreach($tournaments as $tournament)
                @if($tournament->seasons->isNotEmpty())
                    <li>
                        <a href="{{ route('home', ['season' => $tournament->seasons->first()->id]) }}" 
                           class="block p-2 rounded-md text-sm hover:bg-gray-100 dark-mode-hover text-gray-700 dark-mode-text">
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
    <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark-mode-text-heading mb-3">{{ $news->title }}</h1>
        <div class="text-sm text-gray-500 dark-mode-text mb-4 border-b dark-mode-border pb-4">
            <span>Жарияланды: {{ $news->published_at ? $news->published_at->format('d F Y, H:i') : 'Белгісіз' }}</span>
            @if($news->category)
                <span class="ml-4">| &nbsp; Категория: <span class="font-semibold">{{ $news->category->name }}</span></span>
            @endif
        </div>

        <div class="bg-white dark-mode-bg-secondary rounded-lg shadow overflow-hidden">
            @if($news->hasMedia('news'))
                {{-- ВОТ ИЗМЕНЕНИЕ --}}
                <img src="{{ $news->getFirstMediaUrl('news') }}" alt="{{ $news->title }}" class="w-full h-72 object-cover">
            @endif
        </div>
        
        <div class="prose dark:prose-invert max-w-none text-gray-800 dark-mode-text mb-6">
            {!! $news->content !!}
        </div>

        <!-- --- ВОТ НОВЫЙ БЛОК --- -->
        @if($news->teams->isNotEmpty() || $news->players->isNotEmpty())
        <div class="mt-6 border-t dark-mode-border pt-4">
            <h4 class="font-bold text-gray-700 dark-mode-text-heading mb-3">Жаңалыққа қатысты:</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($news->teams as $team)
                    <a href="#" class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $team->name }}</a>
                @endforeach
                @foreach($news->players as $player)
                    <a href="#" class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $player->full_name }}</a>
                @endforeach
            </div>
        </div>
        @endif
        <!-- --- КОНЕЦ НОВОГО БЛОКА --- -->
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