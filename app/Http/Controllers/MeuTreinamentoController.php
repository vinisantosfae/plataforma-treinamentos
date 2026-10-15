<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConcluirTreinamentoRequest;
use App\Http\Resources\MeuTreinamentoResource;
use App\Models\Pessoa;
use App\Services\AtribuicaoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeuTreinamentoController extends Controller
{
    public function __construct(private AtribuicaoService $atribuicoes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $pessoa = $this->pessoa($request);
        $this->atribuicoes->sincronizar($pessoa);

        return MeuTreinamentoResource::collection($this->atribuicoes->listar($pessoa));
    }

    public function show(Request $request, int $treinamentoId): MeuTreinamentoResource
    {
        $pessoa = $this->pessoa($request);
        $this->atribuicoes->sincronizar($pessoa);

        return new MeuTreinamentoResource($this->atribuicoes->encontrar($pessoa, $treinamentoId));
    }

    public function iniciar(Request $request, int $treinamentoId): MeuTreinamentoResource
    {
        $pessoa = $this->pessoa($request);
        $this->atribuicoes->sincronizar($pessoa);

        return new MeuTreinamentoResource(
            $this->atribuicoes->iniciar($this->atribuicoes->encontrar($pessoa, $treinamentoId))
        );
    }

    public function concluir(ConcluirTreinamentoRequest $request, int $treinamentoId): MeuTreinamentoResource
    {
        $pessoa = $this->pessoa($request);
        $this->atribuicoes->sincronizar($pessoa);

        return new MeuTreinamentoResource(
            $this->atribuicoes->concluir($this->atribuicoes->encontrar($pessoa, $treinamentoId))
        );
    }

    private function pessoa(Request $request): Pessoa
    {
        return $request->user()->currentAccessToken()->pessoa()->firstOrFail();
    }
}
