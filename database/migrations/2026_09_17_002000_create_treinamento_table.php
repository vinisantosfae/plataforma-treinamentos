<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('tipo');
            $table->string('video_url_youtube')->nullable();
            $table->string('categoria');
            $table->unsignedInteger('prazo_dias')->nullable();
            $table->unsignedInteger('presenca_minima_percentual')->nullable();
            $table->unsignedInteger('duracao_video_segundos')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('criado_por_pessoa_id_stw')->index();
            $table->timestamps();

            $table->foreign('criado_por_pessoa_id_stw', 'treinamento_criador_fk')
                ->references('pessoa_id_stw')
                ->on('pessoas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento');
    }
};
