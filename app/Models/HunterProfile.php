<?php

namespace App\Models;

use App\Enums\HunterRank;
use Database\Factories\HunterProfileFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HunterProfile extends Model
{
    /** @use HasFactory<HunterProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'codename',
        'rank',
        'current_level',
        'current_xp',
        'total_xp_earned',
        'awakened_at',
        'avatar_meta',
    ];

    protected function casts(): array
    {
        return [
            'rank' => HunterRank::class,
            'awakened_at' => 'datetime',
            'avatar_meta' => AsArrayObject::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stats()
    {
        return $this->hasOne(HunterStats::class);
    }

    public function statHistory()
    {
        return $this->hasMany(HunterStatHistory::class);
    }
}
