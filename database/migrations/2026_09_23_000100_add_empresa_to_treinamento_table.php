<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treinamento', function (Blueprint $table) {
            $table->unsignedInteger('id_empresa')->nullable()->after('ativo')->index();
        });

        DB::table('treinamento')
            ->join('pessoas', 'pessoas.pessoa_id_stw', '=', 'treinamento.criado_por_pessoa_id_stw')
            ->update(['treinamento.id_empresa' => DB::raw('pessoas.empresa_id_stw')]);

        Schema::table('treinamento', function (Blueprint $table) {
            $table->foreign('id_empresa', 'treinamento_empresa_fk')->references('id_stw')->on('empresa_stw');
        });
    }

    public function down(): void
    {
        Schema::table('treinamento', function (Blueprint $table) {
            $table->dropForeign('treinamento_empresa_fk');
            $table->dropColumn('id_empresa');
        });
    }
};
