<?php

// --- Файл: app/Models/Tournament.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'description', 'status'];

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }
}