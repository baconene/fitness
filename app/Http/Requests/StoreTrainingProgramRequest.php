<?php

namespace App\Http\Requests;

use App\Enums\Difficulty;
use App\Enums\TrainingFocus;
use App\Models\TrainingProgram;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TrainingProgram::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'difficulty' => ['required', Rule::enum(Difficulty::class)],
            'focus' => ['required', Rule::enum(TrainingFocus::class)],

            'weeks' => ['required', 'array', 'min:1', 'max:52'],
            'weeks.*.deload' => ['boolean'],

            'weeks.*.days' => ['required', 'array', 'min:1', 'max:7'],
            'weeks.*.days.*.name' => ['nullable', 'string', 'max:120'],
            'weeks.*.days.*.is_rest_day' => ['boolean'],

            'weeks.*.days.*.exercises' => ['array', 'max:20'],
            'weeks.*.days.*.exercises.*.exercise_id' => ['required', 'integer', Rule::exists('exercises', 'id')->where('is_active', true)],
            'weeks.*.days.*.exercises.*.target_sets' => ['required', 'integer', 'between:1,20'],
            'weeks.*.days.*.exercises.*.target_reps_min' => ['required', 'integer', 'between:1,999'],
            'weeks.*.days.*.exercises.*.target_reps_max' => ['required', 'integer', 'between:1,999', 'gte:weeks.*.days.*.exercises.*.target_reps_min'],
            'weeks.*.days.*.exercises.*.target_weight_pct' => ['nullable', 'numeric', 'between:0,200'],
            'weeks.*.days.*.exercises.*.rest_seconds' => ['required', 'integer', 'between:5,900'],
        ];
    }

    /**
     * A program with no trained exercise can never be enrolled in, so reject it here
     * rather than letting enrollment fail later.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasTrainingDay = collect($this->input('weeks', []))
                    ->flatMap(fn ($week) => $week['days'] ?? [])
                    ->contains(fn ($day) => ! ($day['is_rest_day'] ?? false) && ! empty($day['exercises']));

                if (! $hasTrainingDay) {
                    $validator->errors()->add('weeks', 'Add at least one training day with an exercise.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weeks.*.days.*.exercises.*.target_reps_max.gte' => 'The maximum reps must be at least the minimum.',
        ];
    }
}
