<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:200'],
            'content'     => ['nullable', 'string'],
            'course_id'   => ['nullable', 'integer', 'exists:courses,id'],
            'is_favorite' => ['sometimes', 'boolean'],
        ];
    }
}