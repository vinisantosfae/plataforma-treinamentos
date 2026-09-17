<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreinamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'videoUrlYoutube' => $this->video_url_youtube,
            'categoria' => $this->categoria,
            'prazoDias' => $this->prazo_dias,
            'presencaMinimaPercentual' => $this->presenca_minima_percentual,
            'duracaoVideoSegundos' => $this->duracao_video_segundos,
            'ativo' => $this->ativo,
            'criadoPorPessoaIdStw' => $this->criado_por_pessoa_id_stw,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'criador' => new ColaboradorStwResource($this->whenLoaded('criador')),
            'encontros' => TreinamentoEncontroResource::collection($this->whenLoaded('encontros')),
            'prerequisitos' => self::collection($this->whenLoaded('prerequisitos')),
            'requisitoDe' => self::collection($this->whenLoaded('requisitoDe')),
            'cas' => CaStwResource::collection($this->whenLoaded('cas')),
            'atribuicoes' => AtribuicaoTreinamentoResource::collection($this->whenLoaded('atribuicoes')),
        ];
    }
}
