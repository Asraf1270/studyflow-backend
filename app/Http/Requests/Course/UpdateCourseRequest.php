<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('course'));
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:120'],
            'code'     => ['sometimes', 'nullable', 'string', 'max:30'],
            'semester' => ['sometimes', 'nullable', 'string', 'max:40'],
            'color'    => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}