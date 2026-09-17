<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTreinamentoEncontroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'treinamento_id' => [$required, 'integer', 'exists:treinamento,id'],
            'data_inicio' => [$required, 'date'],
            'data_fim' => [$required, 'date', 'after:data_inicio'],
        ];
    }
}
