<?php
// app/Http/Requests/ToggleClearRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleClearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['clear', 'block'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'action is required',
            'action.in' => 'action must be either clear or block',
            'note.max' => 'note must not exceed 1000 characters',
        ];
    }
}
