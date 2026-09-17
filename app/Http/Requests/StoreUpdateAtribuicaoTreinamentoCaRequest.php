<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateAtribuicaoTreinamentoCaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'atribuicao_treinamento_id' => [
                $required,
                'integer',
                'exists:atribuicao_treinamento,id',
                Rule::unique('atribuicao_treinamento_ca', 'atribuicao_treinamento_id')
                    ->where('produto_id_stw', $this->input('produto_id_stw'))
                    ->ignore($this->route('atribuicaoTreinamentoCa')),
            ],
            'produto_id_stw' => [$required, 'integer', 'exists:ca_stw,produto_id_stw'],
        ];
    }
}
