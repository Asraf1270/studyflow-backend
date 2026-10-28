<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'course_id'   => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'due_at'      => ['sometimes', 'nullable', 'date'],
            'priority'    => ['sometimes', Rule::in(Task::PRIORITIES)],
            'status'      => ['sometimes', Rule::in(Task::STATUSES)],
            'reminder_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}