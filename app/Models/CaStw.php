<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CaStw extends Model
{
    protected $table = 'ca_stw';

    protected $fillable = [
        'produto_id_stw',
        'ca_numero',
        'descricao_epi',
        'validade_ca',
        'sincronizado_em',
    ];

    public function treinamentos(): BelongsToMany
    {
        return $this->belongsToMany(
            Treinamento::class,
            'ca_treinamento',
            'produto_id_stw',
            'treinamento_id',
            'produto_id_stw',
            'id'
        );
    }

    public function atribuicoes(): BelongsToMany
    {
        return $this->belongsToMany(
            AtribuicaoTreinamento::class,
            'atribuicao_treinamento_ca',
            'produto_id_stw',
            'atribuicao_treinamento_id',
            'produto_id_stw',
            'id'
        );
    }
}
