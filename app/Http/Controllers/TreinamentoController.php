<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateTreinamentoRequest;
use App\Http\Resources\TreinamentoResource;
use App\Models\Pessoa;
use App\Models\Treinamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TreinamentoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $pessoa = $this->pessoa($request);

        $treinamentos = Treinamento::where('id_empresa', $pessoa->empresa_id_stw)
            ->when(! $pessoa->is_admin, fn ($query) => $query->where('ativo', true))
            ->orderBy('titulo')
            ->get();

        return TreinamentoResource::collection($treinamentos);
    }

    public function show(Request $request, int $id): TreinamentoResource
    {
        return new TreinamentoResource($this->find($request, $id));
    }

    public function store(StoreUpdateTreinamentoRequest $request): JsonResponse
    {
        $pessoa = $this->pessoa($request);

        Treinamento::create([
            ...$request->validated(),
            'id_empresa' => $pessoa->empresa_id_stw,
            'criado_por_pessoa_id_stw' => $pessoa->pessoa_id_stw,
        ]);

        return response()->json(['message' => 'Treinamento cadastrado com sucesso.'], 201);
    }

    public function update(StoreUpdateTreinamentoRequest $request, int $id): JsonResponse
    {
        $this->find($request, $id)->update($request->validated());

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
