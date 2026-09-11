<?php

namespace App\Models;

use Database\Factories\UserProgramEnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgramEnrollment extends Model
{
    /** @use HasFactory<UserProgramEnrollmentFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'training_program_id', 'current_week_number', 'current_day_number', 'started_at', 'status'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime'];
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }
}
