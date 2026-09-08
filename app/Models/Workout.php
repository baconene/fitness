<?php

namespace App\Models;

use Database\Factories\WorkoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    /** @use HasFactory<WorkoutFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'training_program_id',
        'program_day_id',
        'name',
        'scheduled_date',
        'started_at',
        'completed_at',
        'status',
        'total_xp_awarded',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainingProgram()
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function programDay()
    {
        return $this->belongsTo(ProgramDay::class);
    }

    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    public function workoutSets()
    {
        return $this->hasManyThrough(
            WorkoutSet::class,
            WorkoutExercise::class
        );
    }
}
