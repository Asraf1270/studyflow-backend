<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('exam'));
    }

    public function rules(): array
    {
        return [
            'course_id'   => ['sometimes', 'integer', 'exists:courses,id'],
            'title'       => ['sometimes', 'string', 'max:200'],
            'exam_at'     => ['sometimes', 'date'],
            'location'    => ['sometimes', 'nullable', 'string', 'max:120'],
            'notes'       => ['sometimes', 'nullable', 'string'],
            'reminder_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}