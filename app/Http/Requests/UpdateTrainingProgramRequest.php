<?php

namespace App\Http\Requests;

class UpdateTrainingProgramRequest extends StoreTrainingProgramRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('program'));
    }
}
