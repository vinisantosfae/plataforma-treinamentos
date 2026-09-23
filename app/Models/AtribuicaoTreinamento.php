<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AtribuicaoTreinamento extends Model
{
    protected $table = 'atribuicao_treinamento';

    protected $fillable = [
        'treinamento_id',
        'pessoa_id_stw',
        'obrigatorio',
        'data_atribuicao',
        'data_limite',
        'status',
        'termos_aceitos',
        'termos_aceitos_em',
        'tempo_consumido_segundos',
        'data_conclusao',
        'apto',
        'aprovado_por_pessoa_id_stw',
        'aprovado_em',
    ];

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'treinamento_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id_stw', 'pessoa_id_stw');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'aprovado_por_pessoa_id_stw', 'pessoa_id_stw');
    }

    public function cas(): BelongsToMany
    {
        return $this->belongsToMany(
            CaStw::class,
            'atribuicao_treinamento_ca',
            'atribuicao_treinamento_id',
            'produto_id_stw',
            'id',
            'produto_id_stw'
        );
    }
}
