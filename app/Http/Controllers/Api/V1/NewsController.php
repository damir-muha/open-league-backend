<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Resources\V1\NewsResource;
use App\Http\Resources\V1\NewsCollection;
use Carbon\Carbon;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with(['categories'])
            ->where('is_published', true)
            ->where('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return new NewsCollection($news);
    }

    public function show(News $news)
    {
        // ИСПРАВЛЕНО: Дозагружаем связь 'categories'
        $news->loadMissing(['categories', 'teams', 'players']);

        return new NewsResource($news);
    }
}