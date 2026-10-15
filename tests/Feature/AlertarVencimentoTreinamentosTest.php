<?php

namespace Tests\Feature;

use App\Mail\PrazoTreinamentoMail;
use App\Mail\RelatorioPrazosSupervisorMail;
use App\Models\AtribuicaoTreinamento;
use App\Models\Treinamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertarVencimentoTreinamentosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        Mail::fake();

        DB::table('empresa_stw')->insert([
            ['id_stw' => 1001, 'razao_social' => 'ACME'],
            ['id_stw' => 1002, 'razao_social' => 'Beta'],
        ]);
        DB::table('pessoas')->insert([
            ['pessoa_id_stw' => 2001, 'cpf' => '111', 'nome' => 'Ana', 'email' => 'ana@acme.test', 'empresa_id_stw' => 1001, 'is_admin' => true, 'supervisor_nome' => null, 'supervisor_email' => null],
            ['pessoa_id_stw' => 2002, 'cpf' => '222', 'nome' => 'Bruno', 'email' => 'bruno@acme.test', 'empresa_id_stw' => 1001, 'is_admin' => false, 'supervisor_nome' => 'Ana', 'supervisor_email' => 'ana@acme.test'],
            ['pessoa_id_stw' => 2004, 'cpf' => '444', 'nome' => 'Diego', 'email' => null, 'empresa_id_stw' => 1001, 'is_admin' => false, 'supervisor_nome' => 'Ana', 'supervisor_email' => 'ana@acme.test'],
        ]);
    }

    public function test_avisa_colaborador_apenas_de_treinamentos_pendentes_que_vencem_em_ate_tres_dias(): void
    {
        $vencendo = $this->atribuir($this->treinamento('Vence em 2 dias'), 2002, '2026-10-17');
        $this->atribuir($this->treinamento('Vence em 5 dias'), 2002, '2026-10-20');
        $this->atribuir($this->treinamento('Já venceu'), 2002, '2026-10-14');
        $this->atribuir($this->treinamento('Concluído'), 2002, '2026-10-16', concluido: true);
        $this->atribuir($this->treinamento('Inativo', ativo: false), 2002, '2026-10-16');
        $this->atribuir($this->treinamento('Sem prazo'), 2002, null);

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertSent(PrazoTreinamentoMail::class, 1);
        Mail::assertSent(PrazoTreinamentoMail::class, fn (PrazoTreinamentoMail $mail) => $mail->hasTo('bruno@acme.test')
            && $mail->atribuicao->is($vencendo)
            && $mail->diasRestantes === 2);
    }

    public function test_avisa_no_ultimo_dia_do_prazo(): void
    {
        $this->atribuir($this->treinamento('Vence hoje'), 2002, '2026-10-15');

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertSent(PrazoTreinamentoMail::class, fn (PrazoTreinamentoMail $mail) => $mail->diasRestantes === 0);
    }

    public function test_supervisor_recebe_um_relatorio_com_todos_os_colaboradores(): void
    {
        $this->atribuir($this->treinamento('Integração'), 2002, '2026-10-16');
        $this->atribuir($this->treinamento('EPIs'), 2004, '2026-10-18');

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertSent(RelatorioPrazosSupervisorMail::class, 1);
        Mail::assertSent(RelatorioPrazosSupervisorMail::class, fn (RelatorioPrazosSupervisorMail $mail) => $mail->hasTo('ana@acme.test')
            && $mail->itens->pluck('colaborador')->all() === ['Bruno', 'Diego']);
    }

    public function test_colaborador_sem_email_nao_recebe_alerta_mas_aparece_no_relatorio(): void
    {
        $this->atribuir($this->treinamento('EPIs'), 2004, '2026-10-16');

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertNotSent(PrazoTreinamentoMail::class);
        Mail::assertSent(RelatorioPrazosSupervisorMail::class, 1);
    }

    public function test_ignora_treinamento_de_outra_empresa(): void
    {
        $this->atribuir($this->treinamento('Beta', empresa: 1002), 2002, '2026-10-16');

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_relatorio_do_supervisor_pode_ser_desligado(): void
    {
        config(['alertas.notificar_supervisor' => false]);
        $this->atribuir($this->treinamento('Integração'), 2002, '2026-10-16');

        $this->artisan('treinamentos:alertar-vencimento')->assertSuccessful();

        Mail::assertSent(PrazoTreinamentoMail::class, 1);
        Mail::assertNotSent(RelatorioPrazosSupervisorMail::class);
    }

    public function test_e_mails_renderizam_os_dados_do_treinamento(): void
    {
        $atribuicao = $this->atribuir($this->treinamento('Integração'), 2002, '2026-10-17');

        (new PrazoTreinamentoMail($atribuicao->load('treinamento', 'pessoa'), 2))
            ->assertSeeInHtml('Oi, Bruno')
            ->assertSeeInHtml('Integração')
            ->assertSeeInHtml('17/10/2026');
    }

    private function treinamento(string $titulo, int $empresa = 1001, bool $ativo = true): Treinamento
    {
        return Treinamento::create([
            'titulo' => $titulo,
            'tipo' => 'online',
            'categoria' => 'obrigatório',
            'prazo_dias' => 30,
            'video_url_youtube' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
            'ativo' => $ativo,
            'id_empresa' => $empresa,
            'criado_por_pessoa_id_stw' => 2001,
        ]);
    }

    private function atribuir(Treinamento $treinamento, int $pessoa, ?string $dataLimite, bool $concluido = false): AtribuicaoTreinamento
    {
        return AtribuicaoTreinamento::create([
            'treinamento_id' => $treinamento->id,
            'pessoa_id_stw' => $pessoa,
            'obrigatorio' => true,
            'data_atribuicao' => '2026-09-15',
            'data_limite' => $dataLimite,
            'status' => $concluido ? 'concluido' : 'pendente',
            'data_conclusao' => $concluido ? now() : null,
        ]);
    }
}
