<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class News extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_news');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function players()
    {
        return $this->belongsToMany(Player::class);
    }
}