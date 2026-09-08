<?php

namespace App\Models;

use App\Enums\GoalType;
use Database\Factories\FitnessGoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessGoal extends Model
{
    /** @use HasFactory<FitnessGoalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_type',
        'target_value',
        'target_unit',
        'target_date',
        'is_primary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'goal_type' => GoalType::class,
            'target_date' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
