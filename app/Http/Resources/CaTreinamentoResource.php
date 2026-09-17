<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaTreinamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produtoIdStw' => $this->produto_id_stw,
            'treinamentoId' => $this->treinamento_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'ca' => new CaStwResource($this->whenLoaded('ca')),
            'treinamento' => new TreinamentoResource($this->whenLoaded('treinamento')),
        ];
    }
}
