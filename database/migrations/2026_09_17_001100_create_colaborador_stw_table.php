<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colaborador_stw', function (Blueprint $table) {
            $table->unsignedInteger('pessoa_id_stw')->primary();
            $table->string('cpf');
            $table->string('nome');
            $table->unsignedInteger('empresa_id_stw')->index();
            $table->string('perfil');
            $table->dateTime('sincronizado_em')->nullable();

            $table->foreign('empresa_id_stw', 'colaborador_stw_empresa_fk')
                ->references('id_stw')
                ->on('empresa_stw');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaborador_stw');
    }
};
