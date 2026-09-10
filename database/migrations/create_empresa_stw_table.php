<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_stw', function (Blueprint $table) {
            $table->unsignedInteger('id_stw')->primary();
            $table->string('razao_social');
            $table->dateTime('sincronizado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_stw');
    }
};
