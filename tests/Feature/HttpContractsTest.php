<?php

namespace Tests\Feature;

use App\Http\Requests\StoreUpdateTreinamentoRequest;
use App\Http\Resources\TreinamentoResource;
use App\Models\Treinamento;
use Illuminate\Http\Request;
use Tests\TestCase;

class HttpContractsTest extends TestCase
{
    public function test_treinamento_resource_uses_camel_case_and_omits_unloaded_relations(): void
    {
        $treinamento = new Treinamento([
            'titulo' => 'Integração de Segurança',
            'descricao' => 'Treinamento de exemplo.',
            'tipo' => 'online',
            'video_url_youtube' => null,
            'categoria' => 'segurança',
            'prazo_dias' => 30,
            'presenca_minima_percentual' => null,
            'duracao_video_segundos' => 900,
            'ativo' => true,
            'criado_por_pessoa_id_stw' => 2001,
        ]);
        $treinamento->setAttribute('id', 4001);

        $data = (new TreinamentoResource($treinamento))->resolve(Request::create('/'));

        $this->assertSame(4001, $data['id']);
        $this->assertSame('Integração de Segurança', $data['titulo']);
        $this->assertSame(2001, $data['criadoPorPessoaIdStw']);
        $this->assertArrayNotHasKey('criado_por_pessoa_id_stw', $data);
        $this->assertArrayNotHasKey('criador', $data);
        $this->assertArrayNotHasKey('encontros', $data);
    }

    public function test_treinamento_request_requires_fields_on_post_and_allows_partial_patch(): void
    {
        $storeRequest = StoreUpdateTreinamentoRequest::create('/api/treinamentos', 'POST');
        $patchRequest = StoreUpdateTreinamentoRequest::create('/api/treinamentos/1', 'PATCH');

        $this->assertSame('required', $storeRequest->rules()['titulo'][0]);
        $this->assertSame('required', $storeRequest->rules()['criado_por_pessoa_id_stw'][0]);
        $this->assertSame('sometimes', $patchRequest->rules()['titulo'][0]);
        $this->assertSame('sometimes', $patchRequest->rules()['criado_por_pessoa_id_stw'][0]);
    }
}
