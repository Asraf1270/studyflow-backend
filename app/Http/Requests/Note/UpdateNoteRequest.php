<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('note'));
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:200'],
            'content'     => ['sometimes', 'nullable', 'string'],
            'course_id'   => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
            'is_favorite' => ['sometimes', 'boolean'],
        ];
    }
}