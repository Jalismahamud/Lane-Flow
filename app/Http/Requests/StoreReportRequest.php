<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Report;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'type' => ['required', Rule::in([
                Report::TYPE_BLOCKED_LANE,
                Report::TYPE_EMERGENCY_CLEAR,
                Report::TYPE_TEMP_SHIFT
            ])],
            'lane' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'audio' => [
                'nullable',
                'file',
                'mimes:mp3,wav,m4a,ogg,flac',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $allowedMimes = ['audio/mpeg', 'audio/wav', 'audio/mp4', 'audio/x-m4a', 'audio/ogg', 'audio/flac'];
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail('The audio file must be of type mp3, wav, m4a, ogg, or flac.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'latitude is required',
            'latitude.between' => 'latitude must be between -90 and 90',
            'longitude.required' => 'longitude is required',
            'longitude.between' => 'longitude must be between -180 and 180',
            'type.required' => 'type is required',
            'type.in' => 'Invalid report type',
            'lane.required' => 'lane is required',
            'lane.max' => 'lane must not exceed 50 characters',
            'description.max' => 'description must not exceed 1000 characters',
            'duration_minutes.integer' => 'duration_minutes must be an integer',
            'duration_minutes.min' => 'duration_minutes must be at least 1 minute',
            'duration_minutes.max' => 'duration_minutes must not exceed 1440 minutes',
            'audio.required' => 'audio file is required',
            'audio.mimes' => 'audio file must be of type mp3, wav, m4a, ogg, or flac',
            'audio.max' => 'audio file must not exceed 10MB',
        ];
    }
}
