<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questions_count' => ['required', 'integer', 'min:1'],
            'value' => ['required', 'numeric', 'min:0'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.statement' => ['required', 'string'],
            'questions.*.position' => ['required', 'integer', 'min:1', 'distinct'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.options.*.description' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
