<?php

namespace App\Models;

use Database\Factories\OnboardingProgressFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingProgress extends Model
{
    /** @use HasFactory<OnboardingProgressFactory> */
    use HasFactory;

    protected $table = 'onboarding_progress';

    protected $fillable = [
        'user_id',
        'current_step',
        'completed_steps',
        'step_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_steps' => AsArrayObject::class,
            'step_data' => AsArrayObject::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
