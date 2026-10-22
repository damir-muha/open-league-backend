<?php

// --- Файл: app/Models/Venue.php ---
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'capacity'];
}