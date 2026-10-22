@extends('layouts.app')

@section('title', 'Барлық жаңалықтар')

@section('sidebar')
    {{-- Используем тот же сайдбар, что и на главной --}}
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
    <div class="p-4 border-b dark-mode-border">
        <h1 class="text-2xl font-bold text-gray-800 dark-mode-text-heading">Барлық жаңалықтар</h1>
    </div>
    <div class="p-4 space-y-6">
        @forelse ($news as $newsItem)
            <a href="{{ route('news.show', $newsItem) }}" class="block">
                <div class="p-4 rounded-lg hover:bg-gray-50 dark-mode-hover border dark-mode-border">

                    <div >
                        @if($newsItem->hasMedia('news'))
                            <div class=" w-100">
                                {{-- ВОТ ИЗМЕНЕНИЕ --}}
                                <img src="{{ $newsItem->getFirstMediaUrl('news') }}" alt="{{ $newsItem->title }}" class="w-full h-48 sm:h-full object-cover">
                            </div>
                        @endif
                    </div>

                    <h2 class="font-bold text-xl text-gray-800 dark-mode-text-heading mb-2">{{ $newsItem->title }}</h2>
                    <p class="text-sm text-gray-500 dark-mode-text mb-3">
                        {{ $newsItem->published_at ? $newsItem->published_at->format('d.m.Y') : '' }}
                    </p>
                </div>
            </a>
        @empty
            <p class="text-gray-500 dark-mode-text">Жаңалықтар әлі жарияланған жоқ.</p>
        @endforelse

        <div class="mt-6">
            {{ $news->links() }}
        </div>
    </div>
</div>
@endsection

@section('ads')
    {{-- Используем тот же блок рекламы, что и на главной --}}
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