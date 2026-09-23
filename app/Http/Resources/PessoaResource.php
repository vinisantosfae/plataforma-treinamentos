<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PessoaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['pessoaIdStw' => $this->pessoa_id_stw, 'cpf' => $this->cpf, 'nome' => $this->nome, 'empresaIdStw' => $this->empresa_id_stw, 'isAdmin' => $this->is_admin, 'sincronizadoEm' => $this->sincronizado_em, 'empresa' => new EmpresaStwResource($this->whenLoaded('empresa'))];
    }
}
