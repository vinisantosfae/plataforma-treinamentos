<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresencaTreinamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'encontroId' => $this->encontro_id,
            'pessoaIdStw' => $this->pessoa_id_stw,
            'presente' => $this->presente,
            'registradoPorPessoaIdStw' => $this->registrado_por_pessoa_id_stw,
            'registradoEm' => $this->registrado_em,
            'encontro' => new TreinamentoEncontroResource($this->whenLoaded('encontro')),
            'pessoa' => new PessoaResource($this->whenLoaded('pessoa')),
            'registrador' => new PessoaResource($this->whenLoaded('registrador')),
        ];
    }
}
