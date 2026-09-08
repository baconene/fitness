<?php

namespace App\Models;

use App\Enums\RecordType;
use Database\Factories\PersonalRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalRecord extends Model
{
    /** @use HasFactory<PersonalRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'record_type',
        'value',
        'unit',
        'workout_set_id',
        'is_current',
        'achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'record_type' => RecordType::class,
            'is_current' => 'boolean',
            'achieved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workoutSet()
    {
        return $this->belongsTo(WorkoutSet::class);
    }
}
