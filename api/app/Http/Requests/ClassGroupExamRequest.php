<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassGroupExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
        ];
    }
}
