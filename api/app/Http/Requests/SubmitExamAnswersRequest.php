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

    public function messages(): array
    {
        return [
            'answers.required' => 'Envie as respostas da prova.',
            'answers.array' => 'As respostas devem ser enviadas em formato válido.',
            'answers.min' => 'Envie pelo menos uma resposta.',
            'answers.*.exam_question_id.required' => 'Informe a questão de todas as respostas.',
            'answers.*.exam_question_id.integer' => 'A questão informada deve ser válida.',
            'answers.*.exam_question_id.distinct' => 'Cada questão deve ser respondida apenas uma vez.',
            'answers.*.exam_question_id.exists' => 'Uma das questões informadas não foi encontrada.',
            'answers.*.exam_question_option_id.required' => 'Informe a alternativa escolhida em todas as respostas.',
            'answers.*.exam_question_option_id.integer' => 'A alternativa informada deve ser válida.',
            'answers.*.exam_question_option_id.exists' => 'Uma das alternativas informadas não foi encontrada.',
        ];
    }
}
