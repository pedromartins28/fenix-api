<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_group_id' => ['sometimes', 'integer', 'exists:class_groups,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_group_id.integer' => 'A turma informada deve ser válida.',
            'class_group_id.exists' => 'A turma informada não foi encontrada.',
            'per_page.integer' => 'A quantidade de itens por página deve ser um número inteiro.',
            'per_page.min' => 'A quantidade de itens por página deve ser pelo menos 1.',
            'per_page.max' => 'A quantidade de itens por página deve ser no máximo 100.',
            'page.integer' => 'A página informada deve ser um número inteiro.',
            'page.min' => 'A página informada deve ser pelo menos 1.',
        ];
    }
}
