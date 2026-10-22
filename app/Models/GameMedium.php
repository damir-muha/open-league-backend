<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ИСПРАВЛЕНО: Имя класса модели
class GameMedium extends Model
{
    use HasFactory;

    // ИСПРАВЛЕНО: Название таблицы
    protected $table = 'game_media';

    protected $fillable = [
        'game_id',
        'media_type',
        'url',
        'title',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}