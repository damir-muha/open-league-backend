<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people'; // Указываем имя таблицы, т.к. оно не стандартное

    protected $fillable = [
         'user_id','first_name', 'last_name', 'role', 'photo',
        'birth_date', 'citizenship',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_person')->withPivot('role');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'person_team')->withPivot('role')->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}