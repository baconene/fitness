<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\ExerciseType;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_category_id',
        'name',
        'slug',
        'exercise_type',
        'equipment_required',
        'difficulty',
        'primary_muscle',
        'secondary_muscles',
        'instructions',
        'video_url',
        'image_url',
        'contraindications',
        'xp_base_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exercise_type' => ExerciseType::class,
            'difficulty' => Difficulty::class,
            'equipment_required' => AsArrayObject::class,
            'primary_muscle' => AsArrayObject::class,
            'secondary_muscles' => AsArrayObject::class,
            'contraindications' => AsArrayObject::class,
            'is_active' => 'boolean',
        ];
    }
}
