<?php

namespace App\Models;

use Database\Factories\ProgramExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramExercise extends Model
{
    /** @use HasFactory<ProgramExerciseFactory> */
    use HasFactory;

    protected $fillable = ['program_day_id', 'exercise_id', 'order', 'target_sets', 'target_reps_min', 'target_reps_max', 'target_weight_pct', 'rest_seconds'];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function programDay(): BelongsTo
    {
        return $this->belongsTo(ProgramDay::class);
    }
}
