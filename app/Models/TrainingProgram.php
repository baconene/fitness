<?php

namespace App\Models;

use Database\Factories\TrainingProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    /** @use HasFactory<TrainingProgramFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'difficulty', 'focus', 'duration_weeks', 'is_system_program', 'created_by_user_id'];

    protected function casts(): array
    {
        return ['is_system_program' => 'boolean'];
    }

    public function programWeeks(): HasMany
    {
        return $this->hasMany(ProgramWeek::class)->orderBy('week_number');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(UserProgramEnrollment::class);
    }
}
