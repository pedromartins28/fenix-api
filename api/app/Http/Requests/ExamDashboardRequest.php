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
        ];
    }
}
