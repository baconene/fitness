<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddWorkoutExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('workout')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exercise_id' => ['required', 'integer', Rule::exists('exercises', 'id')->where('is_active', true)],
            'sets' => ['required', 'integer', 'between:1,10'],
        ];
    }
}
