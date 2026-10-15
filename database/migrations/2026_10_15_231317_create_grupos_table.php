<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->unsignedInteger('id_empresa')->index();
            $table->timestamps();

            $table->foreign('id_empresa', 'grupos_empresa_fk')
                ->references('id_stw')
                ->on('empresa_stw');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
