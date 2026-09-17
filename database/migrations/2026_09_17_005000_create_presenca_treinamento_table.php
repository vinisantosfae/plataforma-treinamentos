<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presenca_treinamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('encontro_id')->index();
            $table->unsignedInteger('pessoa_id_stw')->index();
            $table->boolean('presente');
            $table->unsignedInteger('registrado_por_pessoa_id_stw')->index();
            $table->dateTime('registrado_em');

            $table->unique(
                ['encontro_id', 'pessoa_id_stw'],
                'presenca_treinamento_encontro_pessoa_unique'
            );

            $table->foreign('encontro_id', 'presenca_treinamento_encontro_fk')
                ->references('id')
                ->on('treinamento_encontro');

            $table->foreign('pessoa_id_stw', 'presenca_treinamento_pessoa_fk')
                ->references('pessoa_id_stw')
                ->on('colaborador_stw');

            $table->foreign('registrado_por_pessoa_id_stw', 'presenca_treinamento_registrador_fk')
                ->references('pessoa_id_stw')
                ->on('colaborador_stw');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presenca_treinamento');
    }
};
