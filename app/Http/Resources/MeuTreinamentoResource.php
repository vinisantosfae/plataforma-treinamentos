<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Treinamento visto pelo colaborador logado: dados do treinamento mais a sua
 * atribuição. O "id" é o do treinamento, usado nas rotas /meus-treinamentos/{id}.
 */
class MeuTreinamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $treinamento = $this->treinamento;
        $pendente = $this->prerequisitoPendente;

        return [
            'id' => $treinamento->id,
            'atribuicaoId' => $this->id,
            'titulo' => $treinamento->titulo,
            'descricao' => $treinamento->descricao,
            'tipo' => $treinamento->tipo,
            'videoUrlYoutube' => $treinamento->video_url_youtube,
            'duracaoVideoSegundos' => $treinamento->duracao_video_segundos,
            'obrigatorio' => (bool) $this->obrigatorio,
            'prazoDias' => $treinamento->prazo_dias,
            'dataAtribuicao' => $this->formatar($this->data_atribuicao, false),
            'dataLimite' => $this->formatar($this->data_limite, false),
            'status' => $this->status,
            'termosAceitos' => (bool) $this->termos_aceitos,
            'dataConclusao' => $this->formatar($this->data_conclusao, true),
            'bloqueado' => $pendente !== null,
            'preRequisito' => $pendente ? ['id' => $pendente->id, 'titulo' => $pendente->titulo] : null,
            'cas' => $treinamento->cas->map(fn ($ca) => $ca->ca_numero)->values(),
        ];
    }

    private function formatar(mixed $valor, bool $comHorario): ?string
    {
        if ($valor === null) {
            return null;
        }

        $data = Carbon::parse($valor);

        return $comHorario ? $data->toIso8601String() : $data->toDateString();
    }
}
