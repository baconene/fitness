<?php

namespace App\Models;

use Database\Factories\ProgramWeekFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramWeek extends Model
{
    /** @use HasFactory<ProgramWeekFactory> */
    use HasFactory;

    protected $fillable = ['training_program_id', 'week_number', 'deload'];

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function programDays(): HasMany
    {
        return $this->hasMany(ProgramDay::class)->orderBy('day_number');
    }
}
