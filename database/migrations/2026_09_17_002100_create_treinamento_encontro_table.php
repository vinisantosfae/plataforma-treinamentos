<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento_encontro', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('treinamento_id')->index();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->timestamps();

            $table->foreign('treinamento_id', 'treinamento_encontro_treinamento_fk')
                ->references('id')
                ->on('treinamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_encontro');
    }
};
