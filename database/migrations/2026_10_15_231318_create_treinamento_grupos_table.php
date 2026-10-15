<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento_grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('treinamento_id')->index();
            $table->unsignedInteger('grupo_id')->index();

            $table->unique(['treinamento_id', 'grupo_id'], 'treinamento_grupos_pair_unique');

            $table->foreign('treinamento_id', 'treinamento_grupos_treinamento_fk')
                ->references('id')
                ->on('treinamento');

            $table->foreign('grupo_id', 'treinamento_grupos_grupo_fk')
                ->references('id')
                ->on('grupos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_grupos');
    }
};
