<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtribuicaoTreinamentoCaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'atribuicaoTreinamentoId' => $this->atribuicao_treinamento_id,
            'produtoIdStw' => $this->produto_id_stw,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'atribuicao' => new AtribuicaoTreinamentoResource($this->whenLoaded('atribuicao')),
            'ca' => new CaStwResource($this->whenLoaded('ca')),
        ];
    }
}
