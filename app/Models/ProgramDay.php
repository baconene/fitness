<?php

namespace App\Models;

use Database\Factories\ProgramDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramDay extends Model
{
    /** @use HasFactory<ProgramDayFactory> */
    use HasFactory;

    protected $fillable = ['program_week_id', 'day_number', 'name', 'is_rest_day'];

    protected function casts(): array
    {
        return ['is_rest_day' => 'boolean'];
    }

    public function programWeek(): BelongsTo
    {
        return $this->belongsTo(ProgramWeek::class);
    }

    public function programExercises(): HasMany
    {
        return $this->hasMany(ProgramExercise::class)->orderBy('order');
    }
}
