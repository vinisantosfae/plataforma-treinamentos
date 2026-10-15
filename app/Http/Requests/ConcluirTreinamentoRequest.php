<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConcluirTreinamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'termos_aceitos' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'termos_aceitos.required' => 'Confirme que assistiu ao treinamento para concluí-lo.',
            'termos_aceitos.accepted' => 'Confirme que assistiu ao treinamento para concluí-lo.',
        ];
    }
}
