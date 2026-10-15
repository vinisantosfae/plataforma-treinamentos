<?php

namespace App\Services;

use App\Models\AtribuicaoTreinamento;
use App\Models\Pessoa;
use App\Models\Treinamento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AtribuicaoService
{
    public const PENDENTE = 'pendente';

    public const EM_ANDAMENTO = 'em_andamento';

    public const CONCLUIDO = 'concluido';

    /**
     * Garante uma atribuição para cada treinamento ativo da empresa da pessoa.
     *
     * Regra provisória: enquanto os GHEs e CAs do STW não estão integrados, todo
     * treinamento ativo da empresa é atribuído a todos os colaboradores. A
     * obrigatoriedade vem da categoria do treinamento. Quando a regra
     * treinamento - CA - grupo existir, é aqui que ela deve restringir a lista.
     */
    public function sincronizar(Pessoa $pessoa): void
    {
        $jaAtribuidos = AtribuicaoTreinamento::where('pessoa_id_stw', $pessoa->pessoa_id_stw)->pluck('treinamento_id');
        $hoje = Carbon::today();

        Treinamento::where('id_empresa', $pessoa->empresa_id_stw)
            ->where('ativo', true)
            ->whereNotIn('id', $jaAtribuidos)
            ->get()
            ->each(fn (Treinamento $treinamento) => AtribuicaoTreinamento::firstOrCreate(
                ['treinamento_id' => $treinamento->id, 'pessoa_id_stw' => $pessoa->pessoa_id_stw],
                [
                    'obrigatorio' => $treinamento->isObrigatorio(),
                    'data_atribuicao' => $hoje->toDateString(),
                    'data_limite' => $treinamento->prazo_dias !== null ? $hoje->copy()->addDays($treinamento->prazo_dias)->toDateString() : null,
                    'status' => self::PENDENTE,
                ]
            ));
    }

    /**
     * Atribuições da pessoa para treinamentos ativos da sua empresa, já com o
     * bloqueio por pré-requisito calculado (relação "prerequisitoPendente": o primeiro
     * pré-requisito ativo ainda não concluído, ou null quando o treinamento está liberado).
     */
    public function listar(Pessoa $pessoa): Collection
    {
        $atribuicoes = AtribuicaoTreinamento::where('pessoa_id_stw', $pessoa->pessoa_id_stw)
            ->whereHas('treinamento', fn ($query) => $query->where('id_empresa', $pessoa->empresa_id_stw)->where('ativo', true))
            ->with(['treinamento.prerequisitos', 'treinamento.cas'])
            ->get();

        $concluidos = $atribuicoes->whereNotNull('data_conclusao')->pluck('treinamento_id')->all();

        return $atribuicoes
            ->each(function (AtribuicaoTreinamento $atribuicao) use ($concluidos) {
                // Pré-requisito inativo não bloqueia: ele nunca poderia ser concluído.
                $pendente = $atribuicao->treinamento->prerequisitos
                    ->first(fn (Treinamento $prerequisito) => $prerequisito->ativo && ! in_array($prerequisito->id, $concluidos));

                // Relação (e não atributo): atributos seriam gravados no banco pelo update().
                $atribuicao->setRelation('prerequisitoPendente', $pendente);
            })
            ->sortBy(fn (AtribuicaoTreinamento $atribuicao) => [$atribuicao->data_limite ?? '9999-12-31', $atribuicao->treinamento->titulo])
            ->values();
    }

    public function encontrar(Pessoa $pessoa, int $treinamentoId): AtribuicaoTreinamento
    {
        $atribuicao = $this->listar($pessoa)->firstWhere('treinamento_id', $treinamentoId);

        abort_if($atribuicao === null, 404, 'Treinamento não encontrado.');

        return $atribuicao;
    }

    public function iniciar(AtribuicaoTreinamento $atribuicao): AtribuicaoTreinamento
    {
        $this->exigirDesbloqueado($atribuicao);

        if ($atribuicao->status === self::PENDENTE) {
            $atribuicao->update(['status' => self::EM_ANDAMENTO]);
        }

        return $atribuicao;
    }

    /**
     * Registra a conclusão. Chamar de novo em um treinamento já concluído não
     * altera a data original (rever o vídeo não refaz a conclusão).
     */
    public function concluir(AtribuicaoTreinamento $atribuicao): AtribuicaoTreinamento
    {
        abort_if(
            $atribuicao->treinamento->tipo === 'presencial',
            422,
            'Treinamentos presenciais são concluídos pelo administrador, após o registro de presença.'
        );

        if ($atribuicao->data_conclusao !== null) {
            return $atribuicao;
        }

        $this->exigirDesbloqueado($atribuicao);

        $agora = now();
        $atribuicao->update([
            'status' => self::CONCLUIDO,
            'termos_aceitos' => true,
            'termos_aceitos_em' => $agora,
            'data_conclusao' => $agora,
        ]);

        return $atribuicao;
    }

    private function exigirDesbloqueado(AtribuicaoTreinamento $atribuicao): void
    {
        abort_if(
            $atribuicao->prerequisitoPendente !== null && $atribuicao->data_conclusao === null,
            422,
            'Conclua o pré-requisito antes deste treinamento.'
        );
    }
}
