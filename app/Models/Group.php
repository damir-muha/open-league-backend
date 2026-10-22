<?php

// --- Файл: app/Models/Group.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['stage_id', 'name', 'inherits_from_group_id', 'inherit_mode'];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'group_team');
    }
    
    public function inheritedGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'inherits_from_group_id');
    }
}