<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'photo', 'birth_date',
        'citizenship', 'position', 'status',
    ];
    
    protected $casts = [
        'birth_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Player $player) {
            if (empty($player->registration_id)) {
                $nextId = (self::max('id') ?? 0) + 1;
                $player->registration_id = 'P-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Player $player) {
            if (!$player->birth_date) return;
            
            $normFirst = self::normalizeKazakhName($player->first_name);
            $normLast = self::normalizeKazakhName($player->last_name);
            
            $duplicates = self::where('id', '!=', $player->id ?? 0)
                ->where('birth_date', $player->birth_date)
                ->get()
                ->filter(function($p) use ($normFirst, $normLast) {
                     return self::normalizeKazakhName($p->first_name) === $normFirst
                         && self::normalizeKazakhName($p->last_name) === $normLast;
                });
                
            if ($duplicates->isNotEmpty()) {
                 $dup = $duplicates->first();
                 throw ValidationException::withMessages([
                     'first_name' => __('messages.duplicate_player', [
                         'name' => "{$dup->first_name} {$dup->last_name}",
                         'date' => $dup->birth_date->format('d.m.Y'),
                     ])
                 ]);
            }
        });
    }

    public static function normalizeKazakhName($name)
    {
        if (!$name) return '';
        $name = mb_strtolower(trim($name), 'UTF-8');
        $search =  ['ә', 'і', 'ң', 'ғ', 'ү', 'ұ', 'қ', 'ө', 'һ', 'ё'];
        $replace = ['а', 'и', 'н', 'г', 'у', 'у', 'к', 'о', 'х', 'е'];
        $name = str_replace($search, $replace, $name);
        return preg_replace('/[^a-zа-я0-9]/u', '', $name);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
   
    public function seasonRosters(): BelongsToMany
    {
        return $this->belongsToMany(SeasonRoster::class, 'player_season_roster');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_player');
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(Suspension::class);
    }

    public function seasonStats(): HasMany
    {
        return $this->hasMany(PlayerSeasonStat::class);
    }

    // --- ВОТ ИСПРАВЛЕННЫЙ МЕТОД ---
    public function isSuspended(): bool
    {
        // Мы обращаемся к $this->suspensions как к СВОЙСТВУ (без скобок).
        // Если данные были загружены через with('suspensions'), Laravel возьмет их из памяти.
        // Если нет — сделает один запрос (как и раньше).
        
        return $this->suspensions->contains(function ($suspension) {
            // Игрок дисквалифицирован, если запись активна И он отбыл меньше матчей, чем положено
            return $suspension->is_active && $suspension->served < $suspension->duration;
        });
    }
    // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
}