<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // exam ownership checked in the controller
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
        ];
    }
}