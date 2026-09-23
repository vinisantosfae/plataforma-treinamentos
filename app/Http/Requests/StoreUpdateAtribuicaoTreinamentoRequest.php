<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateAtribuicaoTreinamentoRequest extends FormRequest
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
                Rule::unique('atribuicao_treinamento', 'treinamento_id')
                    ->where('pessoa_id_stw', $this->input('pessoa_id_stw'))
                    ->ignore($this->route('atribuicaoTreinamento')),
            ],
            'pessoa_id_stw' => [$required, 'integer', 'exists:pessoas,pessoa_id_stw'],
            'obrigatorio' => [$required, 'boolean'],
            'data_atribuicao' => [$required, 'date'],
            'data_limite' => ['sometimes', 'nullable', 'date', 'after_or_equal:data_atribuicao'],
            'status' => [$required, 'string', 'max:255'],
            'termos_aceitos' => ['sometimes', 'boolean'],
            'termos_aceitos_em' => ['sometimes', 'nullable', 'date'],
            'tempo_consumido_segundos' => ['sometimes', 'integer', 'min:0'],
            'data_conclusao' => ['sometimes', 'nullable', 'date'],
            'apto' => ['sometimes', 'nullable', 'boolean'],
            'aprovado_por_pessoa_id_stw' => ['sometimes', 'nullable', 'integer', 'exists:pessoas,pessoa_id_stw'],
            'aprovado_em' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
