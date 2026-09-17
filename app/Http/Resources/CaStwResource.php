<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaStwResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'produtoIdStw' => $this->produto_id_stw,
            'caNumero' => $this->ca_numero,
            'descricaoEpi' => $this->descricao_epi,
            'validadeCa' => $this->validade_ca,
            'sincronizadoEm' => $this->sincronizado_em,
            'treinamentos' => TreinamentoResource::collection($this->whenLoaded('treinamentos')),
            'atribuicoes' => AtribuicaoTreinamentoResource::collection($this->whenLoaded('atribuicoes')),
        ];
    }
}
