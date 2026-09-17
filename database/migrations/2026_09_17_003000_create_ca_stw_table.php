<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_stw', function (Blueprint $table) {
            $table->unsignedInteger('produto_id_stw')->primary();
            $table->string('ca_numero');
            $table->string('descricao_epi');
            $table->date('validade_ca')->nullable();
            $table->dateTime('sincronizado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_stw');
    }
};
