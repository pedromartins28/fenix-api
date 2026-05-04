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
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.statement' => ['required', 'string'],
            'questions.*.position' => ['required', 'integer', 'min:1', 'distinct'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.options.*.description' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da prova.',
            'name.max' => 'O nome da prova deve ter no máximo 255 caracteres.',
            'questions_count.required' => 'Informe a quantidade de questões.',
            'questions_count.integer' => 'A quantidade de questões deve ser um número inteiro.',
            'questions_count.min' => 'A prova deve ter pelo menos uma questão.',
            'value.required' => 'Informe o valor da prova.',
            'value.numeric' => 'O valor da prova deve ser numérico.',
            'value.min' => 'O valor da prova não pode ser negativo.',
            'questions.required' => 'Cadastre as questões da prova.',
            'questions.array' => 'As questões da prova devem ser enviadas em formato válido.',
            'questions.min' => 'A prova deve ter pelo menos uma questão.',
            'questions.*.statement.required' => 'Preencha o enunciado de todas as questões.',
            'questions.*.position.required' => 'Informe a ordem de todas as questões.',
            'questions.*.position.integer' => 'A ordem da questão deve ser um número inteiro.',
            'questions.*.position.min' => 'A ordem da questão deve ser maior que zero.',
            'questions.*.position.distinct' => 'Cada questão deve ter uma ordem única.',
            'questions.*.options.required' => 'Cadastre as alternativas de todas as questões.',
            'questions.*.options.array' => 'As alternativas devem ser enviadas em formato válido.',
            'questions.*.options.min' => 'Cada questão deve ter pelo menos uma alternativa.',
            'questions.*.options.*.description.required' => 'Preencha a descrição de todas as alternativas.',
            'questions.*.options.*.is_correct.required' => 'Marque uma alternativa correta para cada questão.',
            'questions.*.options.*.is_correct.boolean' => 'A marcação de alternativa correta deve ser válida.',
        ];
    }
}
