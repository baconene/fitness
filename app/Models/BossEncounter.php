<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BossEncounter extends Model
{
    protected $fillable = [
        'user_id',
        'boss_id',
        'workout_id',
        'status',
        'current_health',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boss()
    {
        return $this->belongsTo(Boss::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function damageEvents()
    {
        return $this->hasMany(BossDamageEvent::class);
    }
}
