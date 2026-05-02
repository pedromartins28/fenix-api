<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.exam_question_id' => ['required', 'integer', 'distinct', 'exists:exam_questions,id'],
            'answers.*.exam_question_option_id' => ['required', 'integer', 'exists:exam_question_options,id'],
        ];
    }
}
