<?php

namespace App\Models;

use Database\Factories\HunterStatsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HunterStats extends Model
{
    /** @use HasFactory<HunterStatsFactory> */
    use HasFactory;

    protected $fillable = [
        'hunter_profile_id',
        'strength',
        'endurance',
        'agility',
        'vitality',
        'willpower',
        'stat_points_available',
    ];

    public function hunterProfile()
    {
        return $this->belongsTo(HunterProfile::class);
    }
}
