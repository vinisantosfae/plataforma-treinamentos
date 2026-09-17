<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateTreinamentoPrerequisitoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'treinamento_id' => [
                $required,
                'integer',
                'exists:treinamento,id',
                'different:prerequisito_treinamento_id',
                Rule::unique('treinamento_prerequisito', 'treinamento_id')
                    ->where('prerequisito_treinamento_id', $this->input('prerequisito_treinamento_id'))
                    ->ignore($this->route('treinamentoPrerequisito')),
            ],
            'prerequisito_treinamento_id' => [$required, 'integer', 'exists:treinamento,id'],
        ];
    }
}
