<?php

// --- Файл: app/Models/Round.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = ['stage_id', 'name', 'start_date', 'end_date'];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}