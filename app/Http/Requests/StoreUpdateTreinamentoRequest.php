<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTreinamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'titulo' => [$required, 'string', 'max:255'],
            'descricao' => ['sometimes', 'nullable', 'string'],
            'tipo' => [$required, 'string', 'max:255'],
            'video_url_youtube' => ['sometimes', 'nullable', 'url', 'max:255'],
            'categoria' => [$required, 'string', 'max:255'],
            'prazo_dias' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'presenca_minima_percentual' => ['sometimes', 'nullable', 'integer', 'between:0,100'],
            'duracao_video_segundos' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'ativo' => ['sometimes', 'boolean'],
            'criado_por_pessoa_id_stw' => [$required, 'integer', 'exists:colaborador_stw,pessoa_id_stw'],
        ];
    }
}
