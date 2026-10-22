<?php

// --- Файл: app/Models/TransferRequest.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'player_id', 'from_team_id', 'to_team_id', 'status',
        'requested_by_manager_id', 'approved_by_manager_id',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function fromTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'from_team_id');
    }

    public function toTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'to_team_id');
    }
}