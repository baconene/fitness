<?php

namespace App\Models;

use Database\Factories\WorkoutSetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSet extends Model
{
    /** @use HasFactory<WorkoutSetFactory> */
    use HasFactory;

    protected $fillable = [
        'workout_exercise_id',
        'set_number',
        'reps_completed',
        'weight_kg',
        'rpe',
        'duration_seconds',
        'distance_km',
        'is_completed',
        'completed_at',
        'xp_awarded',
        'completion_token',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workoutExercise()
    {
        return $this->belongsTo(WorkoutExercise::class);
    }

    public function personalRecords()
    {
        return $this->hasMany(PersonalRecord::class, 'workout_set_id');
    }
}
