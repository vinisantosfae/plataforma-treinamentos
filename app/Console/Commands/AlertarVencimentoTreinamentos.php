<?php

namespace App\Console\Commands;

use App\Mail\PrazoTreinamentoMail;
use App\Mail\RelatorioPrazosSupervisorMail;
use App\Models\AtribuicaoTreinamento;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class AlertarVencimentoTreinamentos extends Command
{
    protected $signature = 'treinamentos:alertar-vencimento {--dias= : Dias de antecedência (padrão: config alertas.dias_antecedencia)}';

    protected $description = 'Envia e-mails sobre treinamentos não concluídos cujo prazo está perto de vencer';

    public function handle(): int
    {
        $dias = (int) ($this->option('dias') ?? config('alertas.dias_antecedencia'));
        $hoje = Carbon::today(config('alertas.timezone'));

        $atribuicoes = $this->aVencer($hoje, $dias);

        $enviadosColaborador = 0;
        foreach ($atribuicoes as $atribuicao) {
            if ($atribuicao->pessoa->email === null) {
                continue;
            }

            Mail::to($atribuicao->pessoa->email)->send(new PrazoTreinamentoMail($atribuicao, $this->diasRestantes($atribuicao, $hoje)));
            $enviadosColaborador++;
        }

        $enviadosSupervisor = 0;
        if (config('alertas.notificar_supervisor')) {
            $atribuicoes
                ->filter(fn (AtribuicaoTreinamento $atribuicao) => $atribuicao->pessoa->supervisor_email !== null)
                ->groupBy(fn (AtribuicaoTreinamento $atribuicao) => $atribuicao->pessoa->supervisor_email)
                ->each(function (Collection $itens, string $email) use ($hoje, &$enviadosSupervisor) {
                    $linhas = $itens->map(fn (AtribuicaoTreinamento $atribuicao) => [
                        'colaborador' => $atribuicao->pessoa->nome,
                        'treinamento' => $atribuicao->treinamento->titulo,
                        'dataLimite' => Carbon::parse($atribuicao->data_limite)->format('d/m/Y'),
                        'diasRestantes' => $this->diasRestantes($atribuicao, $hoje),
                        'status' => $atribuicao->status,
                    ])->values();

                    Mail::to($email)->send(new RelatorioPrazosSupervisorMail($itens->first()->pessoa->supervisor_nome, $linhas));
                    $enviadosSupervisor++;
                });
        }

        $this->info("Alertas enviados: {$enviadosColaborador} para colaboradores, {$enviadosSupervisor} para supervisores.");

        return self::SUCCESS;
    }

    /**
     * Atribuições não concluídas de treinamentos ativos cujo prazo vence entre
     * hoje e daqui a $dias dias, apenas de treinamentos da empresa do colaborador.
     */
    private function aVencer(Carbon $hoje, int $dias): Collection
    {
        return AtribuicaoTreinamento::query()
            ->whereNull('data_conclusao')
            ->whereBetween('data_limite', [$hoje->toDateString(), $hoje->copy()->addDays($dias)->toDateString()])
            ->whereHas('treinamento', fn ($query) => $query->where('ativo', true))
            ->with(['treinamento', 'pessoa'])
            ->orderBy('data_limite')
            ->get()
            ->filter(fn (AtribuicaoTreinamento $atribuicao) => $atribuicao->pessoa !== null
                && (int) $atribuicao->treinamento->id_empresa === (int) $atribuicao->pessoa->empresa_id_stw)
            ->values();
    }

    private function diasRestantes(AtribuicaoTreinamento $atribuicao, Carbon $hoje): int
    {
        return (int) $hoje->diffInDays(Carbon::parse($atribuicao->data_limite, $hoje->getTimezone()));
    }
}
