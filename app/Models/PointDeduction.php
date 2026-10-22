<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDeduction extends Model 
{
    use HasFactory;

    protected $guarded = [];

    public function season() 
    { 
        return $this->belongsTo(Season::class); 
    }

    public function team() 
    { 
        return $this->belongsTo(Team::class); 
    }
}