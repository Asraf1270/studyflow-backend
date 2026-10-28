<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'               => ['required', 'integer', 'exists:courses,id'],
            'day_of_week'             => ['required', 'integer', 'between:1,7'],
            'start_time'              => ['required', 'date_format:H:i'],
            'end_time'                => ['required', 'date_format:H:i', 'after:start_time'],
            'location'                => ['nullable', 'string', 'max:120'],
            'instructor'              => ['nullable', 'string', 'max:120'],
            'reminder_offset_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ];
    }
}