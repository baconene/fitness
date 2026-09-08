<?php

namespace App\Models;

use Database\Factories\BossFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boss extends Model
{
    /** @use HasFactory<BossFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rank_requirement',
        'max_health',
        'workout_template',
        'xp_reward',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'workout_template' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function encounters()
    {
        return $this->hasMany(BossEncounter::class);
    }
}
