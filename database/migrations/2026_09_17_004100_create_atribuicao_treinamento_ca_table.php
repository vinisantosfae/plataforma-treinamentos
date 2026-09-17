<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta tabela pode existir em bancos criados manualmente antes das migrations.
        // Nesse caso, preservamos os dados e apenas deixamos o Laravel registrar a migration.
        if (Schema::hasTable('atribuicao_treinamento_ca')) {
            return;
        }

        Schema::create('atribuicao_treinamento_ca', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('atribuicao_treinamento_id')->index();
            $table->unsignedInteger('produto_id_stw')->index();
            $table->timestamps();

            $table->unique(
                ['atribuicao_treinamento_id', 'produto_id_stw'],
                'atribuicao_treinamento_ca_pair_unique'
            );

            $table->foreign('atribuicao_treinamento_id', 'atribuicao_treinamento_ca_atribuicao_fk')
                ->references('id')
                ->on('atribuicao_treinamento');

            $table->foreign('produto_id_stw', 'atribuicao_treinamento_ca_ca_fk')
                ->references('produto_id_stw')
                ->on('ca_stw');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atribuicao_treinamento_ca');
    }
};
