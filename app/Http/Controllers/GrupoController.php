<?php

namespace App\Http\Controllers;

use App\Http\Resources\GrupoResource;
use App\Models\Grupo;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GrupoController extends Controller
{
    /**
     * Grupos da empresa de quem está logado, em ordem alfabética.
     * A empresa vem do token; nenhum filtro é enviado pelo cliente.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $pessoa = $this->pessoa($request);

        return GrupoResource::collection(
            Grupo::where('id_empresa', $pessoa->empresa_id_stw)->orderBy('nome')->get()
        );
    }

    private function pessoa(Request $request): Pessoa
    {
        return $request->user()->currentAccessToken()->pessoa()->firstOrFail();
    }
}
