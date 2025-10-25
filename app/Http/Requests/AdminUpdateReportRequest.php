<?php
// app/Http/Requests/AdminUpdateReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Report;
use Illuminate\Validation\Rule;

class AdminUpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'to_status' => ['required', Rule::in([
                Report::STATUS_BLOCKED,
                Report::STATUS_CLEARED,
                Report::STATUS_REMOVED
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
            'official_clear' => ['nullable', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'to_status.required' => 'to_status is required',
            'to_status.in' => 'Invalid to_status',
            'note.max' => 'note must not exceed 1000 characters',
        ];
    }

    protected function failedAuthorization()
    {
        abort(403, 'শুধুমাত্র অ্যাডমিন এই অ্যাকশন করতে পারবেন');
    }
}
