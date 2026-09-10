<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_treinamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id_stw')->index();
            $table->unsignedInteger('treinamento_id')->index();
            $table->timestamps();

            $table->unique(
                ['produto_id_stw', 'treinamento_id'],
                'ca_treinamento_produto_treinamento_unique'
            );

            $table->foreign('produto_id_stw', 'ca_treinamento_ca_fk')
                ->references('produto_id_stw')
                ->on('ca_stw');

            $table->foreign('treinamento_id', 'ca_treinamento_treinamento_fk')
                ->references('id')
                ->on('treinamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_treinamento');
    }
};
