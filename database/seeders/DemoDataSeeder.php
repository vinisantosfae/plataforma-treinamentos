<?php

namespace Database\Seeders;

use App\Models\AtribuicaoTreinamento;
use App\Models\AtribuicaoTreinamentoCa;
use App\Models\CaStw;
use App\Models\CaTreinamento;
use App\Models\ColaboradorStw;
use App\Models\EmpresaStw;
use App\Models\PresencaTreinamento;
use App\Models\Treinamento;
use App\Models\TreinamentoEncontro;
use App\Models\TreinamentoPrerequisito;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();

        $empresaAcme = $this->saveExternal('empresa_stw', 'id_stw', 1001, [
            'razao_social' => 'ACME Equipamentos de Segurança Ltda.',
            'sincronizado_em' => Carbon::parse('2026-09-17 08:00:00'),
        ]);

        $empresaBeta = $this->saveExternal('empresa_stw', 'id_stw', 1002, [
            'razao_social' => 'Beta Serviços Industriais Ltda.',
            'sincronizado_em' => Carbon::parse('2026-09-17 08:05:00'),
        ]);

        $ana = $this->saveExternal('colaborador_stw', 'pessoa_id_stw', 2001, [
            'cpf' => '111.111.111-11',
            'nome' => 'Ana Souza',
            'empresa_id_stw' => $empresaAcme->id_stw,
            'perfil' => 'administrador',
            'sincronizado_em' => Carbon::parse('2026-09-17 08:10:00'),
        ]);

        $bruno = $this->saveExternal('colaborador_stw', 'pessoa_id_stw', 2002, [
            'cpf' => '222.222.222-22',
            'nome' => 'Bruno Lima',
            'empresa_id_stw' => $empresaAcme->id_stw,
            'perfil' => 'colaborador',
            'sincronizado_em' => Carbon::parse('2026-09-17 08:10:00'),
        ]);

        $carla = $this->saveExternal('colaborador_stw', 'pessoa_id_stw', 2003, [
            'cpf' => '333.333.333-33',
            'nome' => 'Carla Oliveira',
            'empresa_id_stw' => $empresaBeta->id_stw,
            'perfil' => 'gestor',
            'sincronizado_em' => Carbon::parse('2026-09-17 08:15:00'),
        ]);

        $caCapacete = $this->saveExternal('ca_stw', 'produto_id_stw', 3001, [
            'ca_numero' => 'CA-12345',
            'descricao_epi' => 'Capacete de segurança com jugular',
            'validade_ca' => Carbon::parse('2028-12-31'),
            'sincronizado_em' => Carbon::parse('2026-09-17 08:20:00'),
        ]);

        $caOculos = $this->saveExternal('ca_stw', 'produto_id_stw', 3002, [
            'ca_numero' => 'CA-67890',
            'descricao_epi' => 'Óculos de proteção contra impacto',
            'validade_ca' => Carbon::parse('2029-06-30'),
            'sincronizado_em' => Carbon::parse('2026-09-17 08:20:00'),
        ]);

        $integracao = $this->saveWithId(new Treinamento(), 4001, [
            'titulo' => 'Integração de Segurança',
            'descricao' => 'Treinamento introdutório sobre segurança e cultura da empresa.',
            'tipo' => 'online',
            'video_url_youtube' => 'https://www.youtube.com/watch?v=demo-integracao',
            'categoria' => 'integração',
            'prazo_dias' => 30,
            'presenca_minima_percentual' => null,
            'duracao_video_segundos' => 900,
            'ativo' => true,
            'criado_por_pessoa_id_stw' => $ana->pessoa_id_stw,
        ]);

        $epi = $this->saveWithId(new Treinamento(), 4002, [
            'titulo' => 'Uso correto de EPIs',
            'descricao' => 'Reconhecimento, uso, higienização e guarda dos equipamentos de proteção.',
            'tipo' => 'presencial',
            'video_url_youtube' => null,
            'categoria' => 'segurança',
            'prazo_dias' => 60,
            'presenca_minima_percentual' => 75,
            'duracao_video_segundos' => null,
            'ativo' => true,
            'criado_por_pessoa_id_stw' => $ana->pessoa_id_stw,
        ]);

        $trabalhoAltura = $this->saveWithId(new Treinamento(), 4003, [
            'titulo' => 'Trabalho em altura',
            'descricao' => 'Boas práticas e requisitos para atividades realizadas em altura.',
            'tipo' => 'presencial',
            'video_url_youtube' => null,
            'categoria' => 'segurança',
            'prazo_dias' => 90,
            'presenca_minima_percentual' => 80,
            'duracao_video_segundos' => null,
            'ativo' => true,
            'criado_por_pessoa_id_stw' => $carla->pessoa_id_stw,
        ]);

        $encontroEpi = $this->saveWithId(new TreinamentoEncontro(), 5001, [
            'treinamento_id' => $epi->id,
            'data_inicio' => Carbon::parse('2026-09-22 09:00:00'),
            'data_fim' => Carbon::parse('2026-09-22 12:00:00'),
        ]);

        $encontroAltura = $this->saveWithId(new TreinamentoEncontro(), 5002, [
            'treinamento_id' => $trabalhoAltura->id,
            'data_inicio' => Carbon::parse('2026-09-25 13:30:00'),
            'data_fim' => Carbon::parse('2026-09-25 17:30:00'),
        ]);

        DB::table('treinamento_prerequisito')->updateOrInsert([
            'treinamento_id' => $trabalhoAltura->id,
            'prerequisito_treinamento_id' => $epi->id,
        ]);

        CaTreinamento::updateOrCreate([
            'produto_id_stw' => $caCapacete->produto_id_stw,
            'treinamento_id' => $epi->id,
        ]);

        CaTreinamento::updateOrCreate([
            'produto_id_stw' => $caOculos->produto_id_stw,
            'treinamento_id' => $epi->id,
        ]);

        $atribuicaoBruno = $this->saveWithId(new AtribuicaoTreinamento(), 6001, [
            'treinamento_id' => $epi->id,
            'pessoa_id_stw' => $bruno->pessoa_id_stw,
            'obrigatorio' => true,
            'data_atribuicao' => Carbon::parse('2026-09-17'),
            'data_limite' => Carbon::parse('2026-11-16'),
            'status' => 'em_andamento',
            'termos_aceitos' => true,
            'termos_aceitos_em' => Carbon::parse('2026-09-17 09:00:00'),
            'tempo_consumido_segundos' => 3600,
            'data_conclusao' => null,
            'apto' => null,
            'aprovado_por_pessoa_id_stw' => null,
            'aprovado_em' => null,
        ]);

        $atribuicaoCarla = $this->saveWithId(new AtribuicaoTreinamento(), 6002, [
            'treinamento_id' => $trabalhoAltura->id,
            'pessoa_id_stw' => $carla->pessoa_id_stw,
            'obrigatorio' => true,
            'data_atribuicao' => Carbon::parse('2026-09-17'),
            'data_limite' => Carbon::parse('2026-12-16'),
            'status' => 'concluido',
            'termos_aceitos' => true,
            'termos_aceitos_em' => Carbon::parse('2026-09-17 09:10:00'),
            'tempo_consumido_segundos' => 14400,
            'data_conclusao' => Carbon::parse('2026-09-25 17:30:00'),
            'apto' => true,
            'aprovado_por_pessoa_id_stw' => $ana->pessoa_id_stw,
            'aprovado_em' => Carbon::parse('2026-09-25 18:00:00'),
        ]);

        AtribuicaoTreinamentoCa::updateOrCreate([
            'atribuicao_treinamento_id' => $atribuicaoBruno->id,
            'produto_id_stw' => $caCapacete->produto_id_stw,
        ]);

        AtribuicaoTreinamentoCa::updateOrCreate([
            'atribuicao_treinamento_id' => $atribuicaoBruno->id,
            'produto_id_stw' => $caOculos->produto_id_stw,
        ]);

        AtribuicaoTreinamentoCa::updateOrCreate([
            'atribuicao_treinamento_id' => $atribuicaoCarla->id,
            'produto_id_stw' => $caCapacete->produto_id_stw,
        ]);

        DB::table('presenca_treinamento')->updateOrInsert(
            [
                'encontro_id' => $encontroEpi->id,
                'pessoa_id_stw' => $bruno->pessoa_id_stw,
            ],
            [
                'presente' => true,
                'registrado_por_pessoa_id_stw' => $ana->pessoa_id_stw,
                'registrado_em' => Carbon::parse('2026-09-22 12:10:00'),
            ]
        );

        DB::table('presenca_treinamento')->updateOrInsert(
            [
                'encontro_id' => $encontroAltura->id,
                'pessoa_id_stw' => $carla->pessoa_id_stw,
            ],
            [
                'presente' => true,
                'registrado_por_pessoa_id_stw' => $ana->pessoa_id_stw,
                'registrado_em' => Carbon::parse('2026-09-25 17:35:00'),
            ]
        );
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@plataforma.test'],
            [
                'name' => 'Administrador de Testes',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::parse('2026-09-17 08:00:00'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'teste@plataforma.test'],
            [
                'name' => 'Usuário de Testes',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::parse('2026-09-17 08:00:00'),
            ]
        );
    }

    private function saveWithId(Model $model, int $id, array $attributes): Model
    {
        $keyName = $model->getKeyName();
        $savedModel = $model->newQuery()->find($id) ?? $model;
        $savedModel->setAttribute($keyName, $id);
        $savedModel->fill($attributes);
        $savedModel->save();

        return $savedModel;
    }

    private function saveExternal(string $table, string $key, int $id, array $attributes): object
    {
        DB::table($table)->updateOrInsert(
            [$key => $id],
            $attributes
        );

        return (object) array_merge([$key => $id], $attributes);
    }
}
