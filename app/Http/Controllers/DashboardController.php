<?php

namespace App\Http\Controllers;

use App\Models\AtribuicaoTreinamento;
use App\Models\Pessoa;
use App\Models\Treinamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        $identity = $this->identity($request);
        $companyId = $identity['empresa']['idStw'];
        $today = Carbon::today();

        $assignments = $this->companyAssignments($companyId);
        $summary = [
            'colaboradores' => Pessoa::where('empresa_id_stw', $companyId)->count(),
            'treinamentosAtivos' => Treinamento::where('id_empresa', $companyId)->where('ativo', true)->count(),
            'colaboradoresPendentes' => (clone $assignments)->whereNull('data_conclusao')->distinct('pessoa_id_stw')->count('pessoa_id_stw'),
            'colaboradoresAtrasados' => (clone $assignments)->whereNull('data_conclusao')->whereDate('data_limite', '<', $today)->distinct('pessoa_id_stw')->count('pessoa_id_stw'),
        ];

        return response()->json([
            'identity' => $identity,
            'summary' => $summary,
            'percentualConcluido' => $this->completionPercentage(clone $assignments),
            'highlights' => array_values(array_filter([
                $this->topTraining(clone $assignments, 'prazoProximo', 'Atenção', 'warning', fn (Builder $query) => $query->whereNull('data_conclusao')->whereBetween('data_limite', [$today, $today->copy()->addDays(7)])),
                $this->topTraining(clone $assignments, 'atrasados', 'Atrasado', 'danger', fn (Builder $query) => $query->whereNull('data_conclusao')->whereDate('data_limite', '<', $today)),
                $this->topTraining(clone $assignments, 'naoIniciaram', 'Acompanhar', 'neutral', fn (Builder $query) => $query->where('status', 'pendente')->whereNull('data_conclusao')),
            ])),
            'recentActivity' => (clone $assignments)->whereNotNull('data_conclusao')->with('pessoa')->latest('data_conclusao')->limit(4)->get()->map(fn ($item) => [
                'descricao' => "{$item->pessoa->nome} concluiu {$item->treinamento->titulo}",
                'dataConclusao' => $item->data_conclusao,
            ])->values(),
        ]);
    }

    public function collaborator(Request $request): JsonResponse
    {
        $identity = $this->identity($request);
        $today = Carbon::today();
        $assignments = AtribuicaoTreinamento::query()->where('pessoa_id_stw', $identity['pessoaIdStw'])->with('treinamento');

        $toCard = fn ($item, string $type) => [
            'id' => $item->treinamento_id,
            'titulo' => $item->treinamento->titulo,
            'categoria' => $item->obrigatorio ? 'Obrigatório' : 'Não obrigatório',
            'dias' => $type === 'vencimento' ? $today->diffInDays(Carbon::parse($item->data_limite)) : Carbon::parse($item->data_limite)->diffInDays($today),
            'tipo' => $type,
            'status' => $item->status,
        ];

        $due = (clone $assignments)->whereNull('data_conclusao')->whereDate('data_limite', '>=', $today)->orderBy('data_limite')->first();
        $overdue = (clone $assignments)->whereNull('data_conclusao')->whereDate('data_limite', '<', $today)->orderBy('data_limite')->first();
        $blocked = (clone $assignments)->whereNull('data_conclusao')->with('treinamento.prerequisitos.atribuicoes')->get()
            ->filter(function (AtribuicaoTreinamento $assignment) use ($identity) {
                return $assignment->treinamento->prerequisitos->contains(function (Treinamento $prerequisite) use ($identity) {
                    return ! $prerequisite->atribuicoes->contains(fn (AtribuicaoTreinamento $item) => $item->pessoa_id_stw === $identity['pessoaIdStw'] && $item->data_conclusao !== null);
                });
            })->map(function (AtribuicaoTreinamento $assignment) {
                $prerequisite = $assignment->treinamento->prerequisitos->first();

                return [
                    'id' => $assignment->treinamento_id,
                    'titulo' => $assignment->treinamento->titulo,
                    'preRequisito' => $prerequisite?->titulo,
                ];
            })->values();

        return response()->json([
            'identity' => $identity,
            'summary' => [
                'aFazer' => (clone $assignments)->where('status', 'pendente')->whereNull('data_conclusao')->count(),
                'emAndamento' => (clone $assignments)->where('status', 'em_andamento')->whereNull('data_conclusao')->count(),
                'concluidos' => (clone $assignments)->whereNotNull('data_conclusao')->count(),
                'atrasados' => (clone $assignments)->whereNull('data_conclusao')->whereDate('data_limite', '<', $today)->count(),
            ],
            'percentualConcluido' => $this->completionPercentage(clone $assignments),
            'attention' => array_values(array_filter([$due ? $toCard($due, 'vencimento') : null, $overdue ? $toCard($overdue, 'atrasado') : null])),
            'emAndamento' => (clone $assignments)->where('status', 'em_andamento')->whereNull('data_conclusao')->get()->map(fn ($item) => [
                'id' => $item->treinamento_id, 'titulo' => $item->treinamento->titulo,
                'categoria' => $item->obrigatorio ? 'Obrigatório' : 'Não obrigatório',
            ])->values(),
            'bloqueados' => $blocked,
        ]);
    }

    private function identity(Request $request): array
    {
        $pessoa = $request->user()->currentAccessToken()->pessoa()->with('empresa')->firstOrFail();

        return [
            'pessoaIdStw' => $pessoa->pessoa_id_stw,
            'nome' => $pessoa->nome,
            'cpf' => $pessoa->cpf,
            'isAdmin' => $pessoa->is_admin,
            'empresa' => [
                'idStw' => $pessoa->empresa->id_stw,
                'razaoSocial' => $pessoa->empresa->razao_social,
            ],
        ];
    }

    private function companyAssignments(int $companyId): Builder
    {
        return AtribuicaoTreinamento::query()->whereHas('pessoa', fn (Builder $query) => $query->where('empresa_id_stw', $companyId))
            ->whereHas('treinamento', fn (Builder $query) => $query->where('id_empresa', $companyId)->where('ativo', true))->with(['treinamento', 'pessoa']);
    }

    private function completionPercentage(Builder $assignments): int
    {
        $total = (clone $assignments)->count();

        return $total === 0 ? 0 : (int) round(((clone $assignments)->whereNotNull('data_conclusao')->count() / $total) * 100);
    }

    private function topTraining(Builder $assignments, string $metric, string $label, string $type, callable $filter): ?array
    {
        $item = $filter($assignments)->selectRaw('treinamento_id, count(*) as total')->groupBy('treinamento_id')->orderByDesc('total')->first();
        if (! $item) {
            return null;
        }

        return ['treinamentoId' => $item->treinamento_id, 'titulo' => $item->treinamento->titulo, 'metrica' => $metric, 'quantidade' => $item->total, 'label' => $label, 'tipo' => $type];
    }
}
