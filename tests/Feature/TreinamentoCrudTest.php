<?php

namespace Tests\Feature;

use App\Models\Treinamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TreinamentoCrudTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'titulo' => 'Uso correto de EPIs',
        'tipo' => 'presencial',
        'categoria' => 'segurança',
        'prazo_dias' => 30,
    ];

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

    public function test_admin_cria_treinamento_na_propria_empresa(): void
    {
        $response = $this->as(2001)->postJson('/api/treinamentos', [...$this->payload, 'id_empresa' => 1002]);

        $response->assertCreated()->assertExactJson(['message' => 'Treinamento cadastrado com sucesso.']);
        $this->assertDatabaseHas('treinamento', ['titulo' => 'Uso correto de EPIs', 'ativo' => true, 'id_empresa' => 1001, 'criado_por_pessoa_id_stw' => 2001]);
    }

    public function test_cadastro_valida_campos_obrigatorios(): void
    {
        $this->as(2001)->postJson('/api/treinamentos', [])->assertUnprocessable()->assertJsonValidationErrors(['titulo', 'tipo', 'categoria']);
    }

    public function test_admin_lista_apenas_treinamentos_da_propria_empresa(): void
    {
        $this->treinamento(1001, 2001, 'ACME ativo');
        $this->treinamento(1001, 2001, 'ACME inativo', false);
        $this->treinamento(1002, 2003, 'Beta');

        $this->as(2001)->getJson('/api/treinamentos')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_colaborador_le_apenas_treinamentos_ativos(): void
    {
        $ativo = $this->treinamento(1001, 2001, 'Ativo');
        $inativo = $this->treinamento(1001, 2001, 'Inativo', false);

        $this->as(2002)->getJson('/api/treinamentos')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ativo->id);
        $this->as(2002)->getJson("/api/treinamentos/{$inativo->id}")->assertNotFound();
    }

    public function test_colaborador_nao_pode_cadastrar_alterar_ou_inativar(): void
    {
        $treinamento = $this->treinamento(1001, 2001, 'Ativo');

        $this->as(2002)->postJson('/api/treinamentos', $this->payload)->assertForbidden();
        $this->as(2002)->patchJson("/api/treinamentos/{$treinamento->id}", ['titulo' => 'Novo'])->assertForbidden();
        $this->as(2002)->deleteJson("/api/treinamentos/{$treinamento->id}")->assertForbidden();
    }

    public function test_admin_altera_treinamento(): void
    {
        $treinamento = $this->treinamento(1001, 2001, 'Antigo');

        $this->as(2001)->patchJson("/api/treinamentos/{$treinamento->id}", ['titulo' => 'Novo'])->assertOk()->assertExactJson(['message' => 'Treinamento atualizado com sucesso.']);
        $this->assertDatabaseHas('treinamento', ['id' => $treinamento->id, 'titulo' => 'Novo']);
    }

    public function test_admin_nao_acessa_treinamento_de_outra_empresa(): void
    {
        $treinamento = $this->treinamento(1002, 2003, 'Beta');

        $this->as(2001)->getJson("/api/treinamentos/{$treinamento->id}")->assertNotFound();
        $this->as(2001)->patchJson("/api/treinamentos/{$treinamento->id}", ['titulo' => 'Novo'])->assertNotFound();
        $this->as(2001)->deleteJson("/api/treinamentos/{$treinamento->id}")->assertNotFound();
    }

    public function test_admin_inativa_treinamento(): void
    {
        $treinamento = $this->treinamento(1001, 2001, 'Ativo');

        $this->as(2001)->deleteJson("/api/treinamentos/{$treinamento->id}")->assertOk()->assertExactJson(['message' => 'Treinamento inativado com sucesso.']);
        $this->assertDatabaseHas('treinamento', ['id' => $treinamento->id, 'ativo' => false]);
    }

    public function test_nao_inativa_pre_requisito_de_treinamento_ativo(): void
    {
        $prerequisito = $this->treinamento(1001, 2001, 'Base');
        $this->treinamento(1001, 2001, 'Avançado')->prerequisitos()->attach($prerequisito->id);

        $this->as(2001)->deleteJson("/api/treinamentos/{$prerequisito->id}")->assertUnprocessable();
        $this->assertDatabaseHas('treinamento', ['id' => $prerequisito->id, 'ativo' => true]);
    }

    public function test_admin_cria_treinamento_com_pre_requisito(): void
    {
        $base = $this->treinamento(1001, 2001, 'Base');

        $this->as(2001)->postJson('/api/treinamentos', [...$this->payload, 'prerequisito_id' => $base->id])->assertCreated();

        $criado = Treinamento::where('titulo', 'Uso correto de EPIs')->firstOrFail();
        $this->assertDatabaseHas('treinamento_prerequisito', ['treinamento_id' => $criado->id, 'prerequisito_treinamento_id' => $base->id]);
    }

    public function test_pre_requisito_precisa_ser_ativo_e_da_mesma_empresa(): void
    {
        $inativo = $this->treinamento(1001, 2001, 'Inativo', false);
        $outraEmpresa = $this->treinamento(1002, 2003, 'Beta');

        $this->as(2001)->postJson('/api/treinamentos', [...$this->payload, 'prerequisito_id' => $inativo->id])->assertUnprocessable()->assertJsonValidationErrors(['prerequisito_id']);
        $this->as(2001)->postJson('/api/treinamentos', [...$this->payload, 'prerequisito_id' => $outraEmpresa->id])->assertUnprocessable()->assertJsonValidationErrors(['prerequisito_id']);
        $this->assertDatabaseCount('treinamento', 2);
    }

    public function test_nao_aceita_pre_requisito_circular(): void
    {
        $a = $this->treinamento(1001, 2001, 'A');
        $b = $this->treinamento(1001, 2001, 'B');
        $c = $this->treinamento(1001, 2001, 'C');
        $b->prerequisitos()->attach($a->id);
        $c->prerequisitos()->attach($b->id);

        $this->as(2001)->patchJson("/api/treinamentos/{$a->id}", ['prerequisito_id' => $a->id])->assertUnprocessable()->assertJsonValidationErrors(['prerequisito_id']);
        $this->as(2001)->patchJson("/api/treinamentos/{$a->id}", ['prerequisito_id' => $c->id])->assertUnprocessable()->assertJsonValidationErrors(['prerequisito_id']);
        $this->assertDatabaseMissing('treinamento_prerequisito', ['treinamento_id' => $a->id]);
    }

    public function test_admin_troca_e_remove_pre_requisito(): void
    {
        $um = $this->treinamento(1001, 2001, 'Um');
        $dois = $this->treinamento(1001, 2001, 'Dois');
        $alvo = $this->treinamento(1001, 2001, 'Alvo');

        $this->as(2001)->patchJson("/api/treinamentos/{$alvo->id}", ['prerequisito_id' => $um->id])->assertOk();
        $this->as(2001)->patchJson("/api/treinamentos/{$alvo->id}", ['prerequisito_id' => $dois->id])->assertOk();
        $this->assertSame([$dois->id], $alvo->prerequisitos()->pluck('treinamento.id')->all());

        $this->as(2001)->patchJson("/api/treinamentos/{$alvo->id}", ['titulo' => 'Sem mexer no vínculo'])->assertOk();
        $this->assertSame([$dois->id], $alvo->prerequisitos()->pluck('treinamento.id')->all());

        $this->as(2001)->patchJson("/api/treinamentos/{$alvo->id}", ['prerequisito_id' => null])->assertOk();
        $this->assertSame(0, $alvo->prerequisitos()->count());
    }

    public function test_listagem_inclui_pre_requisitos(): void
    {
        $base = $this->treinamento(1001, 2001, 'Base');
        $this->treinamento(1001, 2001, 'Avançado')->prerequisitos()->attach($base->id);

        $this->as(2001)->getJson('/api/treinamentos')->assertOk()
            ->assertJsonPath('data.0.titulo', 'Avançado')
            ->assertJsonPath('data.0.prerequisitos.0.id', $base->id)
            ->assertJsonCount(0, 'data.1.prerequisitos');
    }

    private function as(int $pessoaIdStw): self
    {
        $token = User::factory()->create()->createToken('test');
        $token->accessToken->pessoa_id_stw = $pessoaIdStw;
        $token->accessToken->save();

        $this->app['auth']->forgetGuards();

        return $this->withToken($token->plainTextToken);
    }

    private function treinamento(int $empresa, int $criador, string $titulo, bool $ativo = true): Treinamento
    {
        return Treinamento::create([...$this->payload, 'titulo' => $titulo, 'ativo' => $ativo, 'id_empresa' => $empresa, 'criado_por_pessoa_id_stw' => $criador]);
    }
}
