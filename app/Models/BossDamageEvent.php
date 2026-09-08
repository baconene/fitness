<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BossDamageEvent extends Model
{
    protected $fillable = [
        'boss_encounter_id',
        'workout_set_id',
        'source_type',
        'damage_amount',
        'idempotency_key',
    ];

    public function bossEncounter()
    {
        return $this->belongsTo(BossEncounter::class);
    }

    public function workoutSet()
    {
        return $this->belongsTo(WorkoutSet::class);
    }
}
