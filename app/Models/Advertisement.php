<?php

// =================================================================
//      1. Модель: app/Models/Advertisement.php
//      (Без изменений, путь и код корректны)
// =================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $table = 'Advertisements';

    protected $fillable = [
        'title', 'ad_type', 'placement_area', 'image_url', 'target_url',
        'code_snippet', 'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}