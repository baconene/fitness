<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('workout')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'scheduled_date' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'status' => ['sometimes', Rule::in(['planned', 'skipped'])],
            'exercises' => ['sometimes', 'array', 'min:1', 'max:20'],
            'exercises.*.exercise_id' => ['required', 'integer', 'distinct', Rule::exists('exercises', 'id')->where('is_active', true)],
            'exercises.*.sets' => ['required', 'integer', 'between:1,10'],
        ];
    }
}
