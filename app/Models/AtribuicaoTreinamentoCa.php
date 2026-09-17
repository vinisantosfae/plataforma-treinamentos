<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtribuicaoTreinamentoCa extends Model
{
    protected $table = 'atribuicao_treinamento_ca';

    protected $fillable = [
        'atribuicao_treinamento_id',
        'produto_id_stw',
    ];

    public function atribuicao(): BelongsTo
    {
        return $this->belongsTo(AtribuicaoTreinamento::class, 'atribuicao_treinamento_id');
    }

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CaStw::class, 'produto_id_stw', 'produto_id_stw');
    }
}
