<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreinamentoEncontroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treinamentoId' => $this->treinamento_id,
            'dataInicio' => $this->data_inicio,
            'dataFim' => $this->data_fim,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'treinamento' => new TreinamentoResource($this->whenLoaded('treinamento')),
            'presencas' => PresencaTreinamentoResource::collection($this->whenLoaded('presencas')),
        ];
    }
}
