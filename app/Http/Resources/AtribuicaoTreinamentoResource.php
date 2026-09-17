<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtribuicaoTreinamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'treinamentoId' => $this->treinamento_id,
            'pessoaIdStw' => $this->pessoa_id_stw,
            'obrigatorio' => $this->obrigatorio,
            'dataAtribuicao' => $this->data_atribuicao,
            'dataLimite' => $this->data_limite,
            'status' => $this->status,
            'termosAceitos' => $this->termos_aceitos,
            'termosAceitosEm' => $this->termos_aceitos_em,
            'tempoConsumidoSegundos' => $this->tempo_consumido_segundos,
            'dataConclusao' => $this->data_conclusao,
            'apto' => $this->apto,
            'aprovadoPorPessoaIdStw' => $this->aprovado_por_pessoa_id_stw,
            'aprovadoEm' => $this->aprovado_em,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'treinamento' => new TreinamentoResource($this->whenLoaded('treinamento')),
            'pessoa' => new ColaboradorStwResource($this->whenLoaded('pessoa')),
            'aprovador' => new ColaboradorStwResource($this->whenLoaded('aprovador')),
            'cas' => CaStwResource::collection($this->whenLoaded('cas')),
        ];
    }
}
