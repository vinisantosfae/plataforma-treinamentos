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
            // Treinamento online precisa do link do vídeo; o presencial (futuro) não tem vídeo.
            'video_url_youtube' => [$this->isMethod('PATCH') ? 'sometimes' : 'required_if:tipo,online', 'nullable', 'url', 'max:255'],
            'categoria' => [$required, 'string', 'max:255'],
            'prazo_dias' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'presenca_minima_percentual' => ['sometimes', 'nullable', 'integer', 'between:0,100'],
            'duracao_video_segundos' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'ativo' => ['sometimes', 'boolean'],
            'prerequisito_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'video_url_youtube.url' => 'Informe um link válido para o vídeo.',
            'video_url_youtube.required_if' => 'Informe o link do vídeo no YouTube.',
        ];
    }
}
