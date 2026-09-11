<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hunterProfile !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required_without:program_day_id', 'nullable', 'string', 'max:120'],
            'scheduled_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'program_day_id' => ['nullable', 'integer', 'exists:program_days,id'],
            'exercises' => ['required_without:program_day_id', 'array', 'min:1', 'max:20'],
            'exercises.*.exercise_id' => ['required', 'integer', 'distinct', Rule::exists('exercises', 'id')->where('is_active', true)],
            'exercises.*.sets' => ['required', 'integer', 'between:1,10'],
        ];
    }
}
