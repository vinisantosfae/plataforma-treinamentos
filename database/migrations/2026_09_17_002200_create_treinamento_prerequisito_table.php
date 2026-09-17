<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento_prerequisito', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('treinamento_id')->index();
            $table->unsignedInteger('prerequisito_treinamento_id')->index();

            $table->unique(
                ['treinamento_id', 'prerequisito_treinamento_id'],
                'treinamento_prerequisito_pair_unique'
            );

            $table->foreign('treinamento_id', 'treinamento_prerequisito_treinamento_fk')
                ->references('id')
                ->on('treinamento');

            $table->foreign('prerequisito_treinamento_id', 'treinamento_prerequisito_pre_fk')
                ->references('id')
                ->on('treinamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_prerequisito');
    }
};
