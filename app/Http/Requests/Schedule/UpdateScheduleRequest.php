<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('schedule'));
    }

    public function rules(): array
    {
        return [
            'course_id'               => ['sometimes', 'integer', 'exists:courses,id'],
            'day_of_week'             => ['sometimes', 'integer', 'between:1,7'],
            'start_time'              => ['sometimes', 'date_format:H:i'],
            'end_time'                => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'location'                => ['sometimes', 'nullable', 'string', 'max:120'],
            'instructor'              => ['sometimes', 'nullable', 'string', 'max:120'],
            'reminder_offset_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }
}