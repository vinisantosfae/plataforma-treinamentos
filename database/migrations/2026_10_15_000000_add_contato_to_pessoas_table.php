<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * E-mail da pessoa e do seu administrador/supervisor, copiados do retorno do
     * login do STW. São usados pelos alertas de vencimento de treinamento.
     */
    public function up(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nome');
            $table->string('supervisor_nome')->nullable()->after('is_admin');
            $table->string('supervisor_email')->nullable()->after('supervisor_nome');
        });
    }

    public function down(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropColumn(['email', 'supervisor_nome', 'supervisor_email']);
        });
    }
};
