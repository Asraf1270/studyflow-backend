<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'course_id'   => ['nullable', 'integer', 'exists:courses,id'],
            'due_at'      => ['nullable', 'date'],
            'priority'    => ['sometimes', Rule::in(Task::PRIORITIES)],
            'status'      => ['sometimes', Rule::in(Task::STATUSES)],
            'reminder_at' => ['nullable', 'date', 'before_or_equal:due_at'],
        ];
    }
}