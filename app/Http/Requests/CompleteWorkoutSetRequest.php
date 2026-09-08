<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reps' => 'required|integer|min:0|max:999',
            'weight' => 'required|numeric|min:0|max:9999.99',
            'rpe' => 'nullable|integer|min:1|max:10',
            'idempotency_key' => 'nullable|string|uuid',
        ];
    }
}
