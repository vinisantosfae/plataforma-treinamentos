<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreinamentoPrerequisitoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treinamentoId' => $this->treinamento_id,
            'prerequisitoTreinamentoId' => $this->prerequisito_treinamento_id,
            'treinamento' => new TreinamentoResource($this->whenLoaded('treinamento')),
            'prerequisito' => new TreinamentoResource($this->whenLoaded('prerequisito')),
        ];
    }
}
