<?php

namespace Tests\Feature;

use App\Models\AtribuicaoTreinamento;
use App\Models\Treinamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeuTreinamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('empresa_stw')->insert([
            ['id_stw' => 1001, 'razao_social' => 'ACME'],
            ['id_stw' => 1002, 'razao_social' => 'Beta'],
        ]);
        DB::table('pessoas')->insert([
            ['pessoa_id_stw' => 2001, 'cpf' => '111', 'nome' => 'Ana', 'empresa_id_stw' => 1001, 'is_admin' => true],
            ['pessoa_id_stw' => 2002, 'cpf' => '222', 'nome' => 'Bruno', 'empresa_id_stw' => 1001, 'is_admin' => false],
            ['pessoa_id_stw' => 2003, 'cpf' => '333', 'nome' => 'Carla', 'empresa_id_stw' => 1002, 'is_admin' => true],
        ]);
    }

    public function test_exige_autenticacao(): void
    {
        $this->getJson('/api/meus-treinamentos')->assertUnauthorized();
        $this->postJson('/api/meus-treinamentos/1/concluir', ['termos_aceitos' => true])->assertUnauthorized();
    }

    public function test_lista_cria_atribuicoes_apenas_para_treinamentos_ativos_da_propria_empresa(): void
    {
        $this->treinamento('ACME ativo');
        $this->treinamento('ACME inativo', ativo: false);
        $this->treinamento('Beta', empresa: 1002, criador: 2003);

        $this->as(2002)->getJson('/api/meus-treinamentos')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'ACME ativo')
            ->assertJsonPath('data.0.status', 'pendente')
            ->assertJsonPath('data.0.bloqueado', false);
        $this->assertDatabaseCount('atribuicao_treinamento', 1);
    }

    public function test_listar_de_novo_nao_duplica_atribuicoes(): void
    {
        $this->treinamento('Um');
        $this->treinamento('Dois');

        $this->as(2002)->getJson('/api/meus-treinamentos')->assertOk();
        $this->as(2002)->getJson('/api/meus-treinamentos')->assertOk()->assertJsonCount(2, 'data');
        $this->assertDatabaseCount('atribuicao_treinamento', 2);
    }

    public function test_obrigatoriedade_e_prazo_vem_do_cadastro_do_treinamento(): void
    {
        $this->treinamento('Obrigatório', categoria: 'obrigatório', prazo: 10);
        $this->treinamento('Opcional', categoria: 'não obrigatório', prazo: null);

        $dados = $this->as(2002)->getJson('/api/meus-treinamentos')->assertOk()->json('data');
        $porTitulo = collect($dados)->keyBy('titulo');

        $this->assertTrue($porTitulo['Obrigatório']['obrigatorio']);
        $this->assertSame(now()->addDays(10)->toDateString(), $porTitulo['Obrigatório']['dataLimite']);
        $this->assertFalse($porTitulo['Opcional']['obrigatorio']);
        $this->assertNull($porTitulo['Opcional']['dataLimite']);
    }

    public function test_cada_pessoa_ve_apenas_a_propria_atribuicao(): void
    {
        $treinamento = $this->treinamento('Compartilhado');

        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/iniciar")->assertOk();

        $this->as(2001)->getJson("/api/meus-treinamentos/{$treinamento->id}")->assertOk()->assertJsonPath('data.status', 'pendente');
        $this->as(2002)->getJson("/api/meus-treinamentos/{$treinamento->id}")->assertOk()->assertJsonPath('data.status', 'em_andamento');
    }

    public function test_detalhe_devolve_video_e_nao_acessa_inativo_ou_de_outra_empresa(): void
    {
        $ativo = $this->treinamento('Ativo');
        $inativo = $this->treinamento('Inativo', ativo: false);
        $beta = $this->treinamento('Beta', empresa: 1002, criador: 2003);

        $this->as(2002)->getJson("/api/meus-treinamentos/{$ativo->id}")->assertOk()
            ->assertJsonPath('data.videoUrlYoutube', 'https://www.youtube.com/watch?v=M7lc1UVf-VE');
        $this->as(2002)->getJson("/api/meus-treinamentos/{$inativo->id}")->assertNotFound();
        $this->as(2002)->getJson("/api/meus-treinamentos/{$beta->id}")->assertNotFound();
    }

    public function test_pre_requisito_pendente_bloqueia_e_concluir_libera(): void
    {
        $base = $this->treinamento('Base');
        $avancado = $this->treinamento('Avançado');
        $avancado->prerequisitos()->attach($base->id);

        $this->as(2002)->getJson("/api/meus-treinamentos/{$avancado->id}")->assertOk()
            ->assertJsonPath('data.bloqueado', true)
            ->assertJsonPath('data.preRequisito.id', $base->id);
        $this->as(2002)->postJson("/api/meus-treinamentos/{$avancado->id}/iniciar")->assertUnprocessable();
        $this->as(2002)->postJson("/api/meus-treinamentos/{$avancado->id}/concluir", ['termos_aceitos' => true])->assertUnprocessable();

        $this->as(2002)->postJson("/api/meus-treinamentos/{$base->id}/concluir", ['termos_aceitos' => true])->assertOk();

        $this->as(2002)->getJson("/api/meus-treinamentos/{$avancado->id}")->assertOk()
            ->assertJsonPath('data.bloqueado', false)
            ->assertJsonPath('data.preRequisito', null);
        $this->as(2002)->postJson("/api/meus-treinamentos/{$avancado->id}/iniciar")->assertOk()->assertJsonPath('data.status', 'em_andamento');
    }

    public function test_pre_requisito_inativo_nao_bloqueia(): void
    {
        $base = $this->treinamento('Base', ativo: false);
        $avancado = $this->treinamento('Avançado');
        $avancado->prerequisitos()->attach($base->id);

        $this->as(2002)->getJson("/api/meus-treinamentos/{$avancado->id}")->assertOk()->assertJsonPath('data.bloqueado', false);
    }

    public function test_iniciar_muda_para_em_andamento_e_e_idempotente(): void
    {
        $treinamento = $this->treinamento('Vídeo');

        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/iniciar")->assertOk()->assertJsonPath('data.status', 'em_andamento');
        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/iniciar")->assertOk()->assertJsonPath('data.status', 'em_andamento');
    }

    public function test_concluir_exige_aceite_dos_termos(): void
    {
        $treinamento = $this->treinamento('Vídeo');

        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/concluir")->assertUnprocessable()->assertJsonValidationErrors(['termos_aceitos']);
        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/concluir", ['termos_aceitos' => false])->assertUnprocessable()->assertJsonValidationErrors(['termos_aceitos']);
        $this->assertDatabaseMissing('atribuicao_treinamento', ['status' => 'concluido']);
    }

    public function test_concluir_registra_aceite_e_data_sem_alterar_na_repeticao(): void
    {
        $treinamento = $this->treinamento('Vídeo');

        $primeira = $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/concluir", ['termos_aceitos' => true])
            ->assertOk()
            ->assertJsonPath('data.status', 'concluido')
            ->assertJsonPath('data.termosAceitos', true);
        $this->assertNotNull($primeira->json('data.dataConclusao'));
        $this->assertDatabaseHas('atribuicao_treinamento', ['treinamento_id' => $treinamento->id, 'pessoa_id_stw' => 2002, 'status' => 'concluido', 'termos_aceitos' => true]);

        $this->travel(2)->days();
        $segunda = $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/concluir", ['termos_aceitos' => true])->assertOk();
        $this->assertSame($primeira->json('data.dataConclusao'), $segunda->json('data.dataConclusao'));
    }

    public function test_treinamento_presencial_nao_e_concluido_pelo_colaborador(): void
    {
        $treinamento = $this->treinamento('Presencial', tipo: 'presencial', video: null);

        $this->as(2002)->postJson("/api/meus-treinamentos/{$treinamento->id}/concluir", ['termos_aceitos' => true])->assertUnprocessable();
        $this->assertNull(AtribuicaoTreinamento::firstOrFail()->data_conclusao);
    }

    public function test_dashboard_do_colaborador_conta_as_atribuicoes_sincronizadas(): void
    {
        $this->treinamento('Um');
        $this->treinamento('Dois');

        $this->as(2002)->getJson('/api/dashboard/colaborador')->assertOk()->assertJsonPath('summary.aFazer', 2);
    }

    private function as(int $pessoaIdStw): self
    {
        $token = User::factory()->create()->createToken('test');
        $token->accessToken->pessoa_id_stw = $pessoaIdStw;
        $token->accessToken->save();

        $this->app['auth']->forgetGuards();

        return $this->withToken($token->plainTextToken);
    }

    private function treinamento(
        string $titulo,
        int $empresa = 1001,
        int $criador = 2001,
        bool $ativo = true,
        string $categoria = 'obrigatório',
        string $tipo = 'online',
        ?int $prazo = 30,
        ?string $video = 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
    ): Treinamento {
        return Treinamento::create([
            'titulo' => $titulo,
            'tipo' => $tipo,
            'categoria' => $categoria,
            'prazo_dias' => $prazo,
            'video_url_youtube' => $video,
            'ativo' => $ativo,
            'id_empresa' => $empresa,
            'criado_por_pessoa_id_stw' => $criador,
        ]);
    }
}
