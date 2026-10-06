<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateTreinamentoRequest;
use App\Http\Resources\TreinamentoResource;
use App\Models\Pessoa;
use App\Models\Treinamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TreinamentoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $pessoa = $this->pessoa($request);

        $treinamentos = Treinamento::where('id_empresa', $pessoa->empresa_id_stw)
            ->when(! $pessoa->is_admin, fn ($query) => $query->where('ativo', true))
            ->with('prerequisitos')
            ->orderBy('titulo')
            ->get();

        return TreinamentoResource::collection($treinamentos);
    }

    public function show(Request $request, int $id): TreinamentoResource
    {
        return new TreinamentoResource($this->find($request, $id)->load('prerequisitos'));
    }

    public function store(StoreUpdateTreinamentoRequest $request): JsonResponse
    {
        $pessoa = $this->pessoa($request);

        $dados = $request->validated();

        DB::transaction(function () use ($dados, $pessoa) {
            $treinamento = Treinamento::create([
                ...Arr::except($dados, ['prerequisito_id']),
                'id_empresa' => $pessoa->empresa_id_stw,
                'criado_por_pessoa_id_stw' => $pessoa->pessoa_id_stw,
            ]);

            if (! empty($dados['prerequisito_id'])) {
                $this->definirPrerequisito($treinamento, $dados['prerequisito_id'], $pessoa);
            }
        });

        return response()->json(['message' => 'Treinamento cadastrado com sucesso.'], 201);
    }

    public function update(StoreUpdateTreinamentoRequest $request, int $id): JsonResponse
    {
        $pessoa = $this->pessoa($request);
        $treinamento = $this->find($request, $id);
        $dados = $request->validated();

        DB::transaction(function () use ($treinamento, $dados, $pessoa) {
            $treinamento->update(Arr::except($dados, ['prerequisito_id']));

            if (array_key_exists('prerequisito_id', $dados)) {
                $this->definirPrerequisito($treinamento, $dados['prerequisito_id'], $pessoa);
            }
        });

        return response()->json(['message' => 'Treinamento atualizado com sucesso.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $treinamento = $this->find($request, $id);

        abort_if(
            $treinamento->requisitoDe()->where('ativo', true)->exists(),
            422,
            'Este treinamento é pré-requisito de outro treinamento ativo.'
        );

        $treinamento->update(['ativo' => false]);

        return response()->json(['message' => 'Treinamento inativado com sucesso.']);
    }

    private function definirPrerequisito(Treinamento $treinamento, ?int $prerequisitoId, Pessoa $pessoa): void
    {
        if ($prerequisitoId === null) {
            $treinamento->prerequisitos()->detach();

            return;
        }

        $prerequisito = Treinamento::where('id_empresa', $pessoa->empresa_id_stw)
            ->where('ativo', true)
            ->find($prerequisitoId);

        if (! $prerequisito) {
            throw ValidationException::withMessages([
                'prerequisito_id' => 'Selecione um treinamento ativo da sua empresa.',
            ]);
        }

        if ($this->criaCiclo($treinamento->id, $prerequisito->id)) {
            throw ValidationException::withMessages([
                'prerequisito_id' => 'O pré-requisito não pode depender deste treinamento.',
            ]);
        }

        $treinamento->prerequisitos()->sync([$prerequisito->id]);
    }

    /**
     * Percorre a cadeia de pré-requisitos a partir do candidato. Se em algum
     * ponto chegar ao próprio treinamento, haveria uma dependência circular.
     */
    private function criaCiclo(int $treinamentoId, int $prerequisitoId): bool
    {
        $pendentes = [$prerequisitoId];
        $visitados = [];

        while ($pendentes) {
            $atual = array_pop($pendentes);

            if ($atual === $treinamentoId) {
                return true;
            }

            if (isset($visitados[$atual])) {
                continue;
            }

            $visitados[$atual] = true;

            $anteriores = DB::table('treinamento_prerequisito')
                ->where('treinamento_id', $atual)
                ->pluck('prerequisito_treinamento_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $pendentes = [...$pendentes, ...$anteriores];
        }

        return false;
    }

    private function pessoa(Request $request): Pessoa
    {
        return $request->user()->currentAccessToken()->pessoa()->firstOrFail();
    }

    private function find(Request $request, int $id): Treinamento
    {
        $pessoa = $this->pessoa($request);

        return Treinamento::where('id_empresa', $pessoa->empresa_id_stw)
            ->when(! $pessoa->is_admin, fn ($query) => $query->where('ativo', true))
            ->findOrFail($id);
    }
}
