<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workout = $this->route('workout');
        $set = $this->route('set');

        return $this->user()?->can('update', $workout)
            && $set->workoutExercise->workout_id === $workout->id;
    }

    public function rules(): array
    {
        $timed = in_array($this->route('set')->workoutExercise->exercise->exercise_type, [
            ExerciseType::Cardio, ExerciseType::Mobility, ExerciseType::Stretching,
        ], true);

        return [
            'reps' => [Rule::requiredIf(! $timed && ! $this->filled('duration_seconds')), 'nullable', 'integer', 'min:1', 'max:999'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'duration_seconds' => [Rule::requiredIf($timed), 'nullable', 'integer', 'between:1,86400'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'rpe' => ['nullable', 'integer', 'between:1,10'],
            'idempotency_key' => ['nullable', 'uuid'],
        ];
    }
}
