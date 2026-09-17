<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdatePresencaTreinamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'encontro_id' => [
                $required,
                'integer',
                'exists:treinamento_encontro,id',
                Rule::unique('presenca_treinamento', 'encontro_id')
                    ->where('pessoa_id_stw', $this->input('pessoa_id_stw'))
                    ->ignore($this->route('presencaTreinamento')),
            ],
            'pessoa_id_stw' => [$required, 'integer', 'exists:colaborador_stw,pessoa_id_stw'],
            'presente' => [$required, 'boolean'],
            'registrado_por_pessoa_id_stw' => [$required, 'integer', 'exists:colaborador_stw,pessoa_id_stw'],
            'registrado_em' => [$required, 'date'],
        ];
    }
}
