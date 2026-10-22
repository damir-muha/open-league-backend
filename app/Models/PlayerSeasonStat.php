<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSeasonStat extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function season(): BelongsTo
    { 
        return $this->belongsTo(Season::class); 
    }

    public function team(): BelongsTo
    { 
        return $this->belongsTo(Team::class); 
    }

}