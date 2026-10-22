<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\News;
use App\Models\Tournament;

class NewsController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::whereHas('seasons')->get();
        $advertisements = Advertisement::where('is_active', true)->where('placement_area', 'sidebar')->get();
        
        $news = News::latest('published_at')->paginate(10);

        return view('news.index', compact('news', 'tournaments', 'advertisements'));
    }

    public function show(News $news)
    {
        $tournaments = Tournament::whereHas('seasons')->get();
        $advertisements = Advertisement::where('is_active', true)->where('placement_area', 'sidebar')->get();

        // --- ВОТ ГЛАВНОЕ ИСПРАВЛЕНИЕ ---
        // Загружаем правильные связанные данные для новости
        $news->load('categories', 'players', 'teams');
        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

        return view('news.show', compact('news', 'tournaments', 'advertisements'));
    }
}