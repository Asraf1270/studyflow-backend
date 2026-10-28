<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('exam_topic'));
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:200'],
            'is_completed' => ['sometimes', 'boolean'],
        ];
    }
}