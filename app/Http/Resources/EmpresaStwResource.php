<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaStwResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'idStw' => $this->id_stw,
            'razaoSocial' => $this->razao_social,
            'sincronizadoEm' => $this->sincronizado_em,
            'colaboradores' => PessoaResource::collection($this->whenLoaded('colaboradores')),
        ];
    }
}
