<?php
// app/Http/Requests/UpdateReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:1000'],
            'lane' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'lane.required' => 'lane is required',
            'lane.max' => 'lane must not exceed 50 characters',
            'description.max' => 'description must not exceed 1000 characters',
        ];
    }
}
