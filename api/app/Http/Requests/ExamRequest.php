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
            'name' => ['required', 'string', 'max:255'],
            'questions_count' => ['required', 'integer', 'min:1'],
            'value' => ['required', 'numeric', 'min:0'],
            'class_group_ids' => ['required', 'array', 'min:1'],
            'class_group_ids.*' => ['required', 'integer', 'distinct', 'exists:class_groups,id'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.statement' => ['required', 'string'],
            'questions.*.position' => ['required', 'integer', 'min:1', 'distinct'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.options.*.description' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
