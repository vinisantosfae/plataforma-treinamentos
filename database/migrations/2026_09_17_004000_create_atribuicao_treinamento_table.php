<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atribuicao_treinamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('treinamento_id')->index();
            $table->unsignedInteger('pessoa_id_stw')->index();
            $table->boolean('obrigatorio');
            $table->date('data_atribuicao');
            $table->date('data_limite')->nullable();
            $table->string('status');
            $table->boolean('termos_aceitos')->default(false);
            $table->dateTime('termos_aceitos_em')->nullable();
            $table->unsignedInteger('tempo_consumido_segundos')->default(0);
            $table->dateTime('data_conclusao')->nullable();
            $table->boolean('apto')->nullable();
            $table->unsignedInteger('aprovado_por_pessoa_id_stw')->nullable()->index();
            $table->dateTime('aprovado_em')->nullable();
            $table->timestamps();

            $table->unique(
                ['treinamento_id', 'pessoa_id_stw'],
                'atribuicao_treinamento_treinamento_pessoa_unique'
            );

            $table->foreign('treinamento_id', 'atribuicao_treinamento_treinamento_fk')
                ->references('id')
                ->on('treinamento');

            $table->foreign('pessoa_id_stw', 'atribuicao_treinamento_pessoa_fk')
                ->references('pessoa_id_stw')
                ->on('pessoas');

            $table->foreign('aprovado_por_pessoa_id_stw', 'atribuicao_treinamento_aprovador_fk')
                ->references('pessoa_id_stw')
                ->on('pessoas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atribuicao_treinamento');
    }
};
