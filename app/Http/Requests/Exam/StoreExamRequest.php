<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'   => ['required', 'integer', 'exists:courses,id'],
            'title'       => ['required', 'string', 'max:200'],
            'exam_at'     => ['required', 'date'],
            'location'    => ['nullable', 'string', 'max:120'],
            'notes'       => ['nullable', 'string'],
            'reminder_at' => ['nullable', 'date', 'before_or_equal:exam_at'],
        ];
    }
}