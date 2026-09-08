<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\TrainingFocus;
use Database\Factories\TrainingPreferenceFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingPreference extends Model
{
    /** @use HasFactory<TrainingPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'experience_level',
        'days_per_week',
        'preferred_days',
        'session_duration_minutes',
        'training_focus',
    ];

    protected function casts(): array
    {
        return [
            'experience_level' => Difficulty::class,
            'preferred_days' => AsArrayObject::class,
            'training_focus' => TrainingFocus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
