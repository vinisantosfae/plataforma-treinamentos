<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateCaTreinamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'produto_id_stw' => [
                $required,
                'integer',
                'exists:ca_stw,produto_id_stw',
                Rule::unique('ca_treinamento', 'produto_id_stw')
                    ->where('treinamento_id', $this->input('treinamento_id'))
                    ->ignore($this->route('caTreinamento')),
            ],
            'treinamento_id' => [$required, 'integer', 'exists:treinamento,id'],
        ];
    }
}
