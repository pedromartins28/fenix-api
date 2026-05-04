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

    public function messages(): array
    {
        return [
            'exam_id.required' => 'Informe a prova que será vinculada à turma.',
            'exam_id.integer' => 'A prova informada deve ser válida.',
            'exam_id.exists' => 'A prova informada não foi encontrada.',
        ];
    }
}
