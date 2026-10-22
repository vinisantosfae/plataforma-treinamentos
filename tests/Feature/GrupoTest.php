<?php

namespace Tests\Feature;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrupoTest extends TestCase
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
        $this->getJson('/api/grupos')->assertUnauthorized();
    }

    public function test_lista_apenas_os_grupos_da_empresa_de_quem_esta_logado(): void
    {
        Grupo::create(['nome' => 'Produção', 'id_empresa' => 1001]);
        Grupo::create(['nome' => 'Manutenção', 'id_empresa' => 1001]);
        Grupo::create(['nome' => 'Obras', 'id_empresa' => 1002]);

        $this->as(2001)->getJson('/api/grupos')->assertOk()->assertJsonCount(2, 'data');
        $this->as(2003)->getJson('/api/grupos')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nome', 'Obras');
    }

    public function test_devolve_os_grupos_em_ordem_alfabetica_com_id_nome_e_empresa(): void
    {
        $producao = Grupo::create(['nome' => 'Produção', 'id_empresa' => 1001]);
        Grupo::create(['nome' => 'Almoxarifado', 'id_empresa' => 1001]);

        $this->as(2001)->getJson('/api/grupos')->assertOk()
            ->assertJsonPath('data.0.nome', 'Almoxarifado')
            ->assertJsonPath('data.1', ['id' => $producao->id, 'nome' => 'Produção', 'idEmpresa' => 1001]);
    }

    public function test_colaborador_tambem_consulta_os_grupos_da_propria_empresa(): void
    {
        Grupo::create(['nome' => 'Produção', 'id_empresa' => 1001]);

        $this->as(2002)->getJson('/api/grupos')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_sem_grupos_devolve_lista_vazia(): void
    {
        $this->as(2001)->getJson('/api/grupos')->assertOk()->assertExactJson(['data' => []]);
    }

    private function as(int $pessoaIdStw): self
    {
        $token = User::factory()->create()->createToken('test');
        $token->accessToken->pessoa_id_stw = $pessoaIdStw;
        $token->accessToken->save();

        $this->app['auth']->forgetGuards();

        return $this->withToken($token->plainTextToken);
    }
}
