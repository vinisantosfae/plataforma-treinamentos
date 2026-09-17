<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresencaTreinamento extends Model
{
    protected $table = 'presenca_treinamento';

    protected $fillable = [
        'encontro_id',
        'pessoa_id_stw',
        'presente',
        'registrado_por_pessoa_id_stw',
        'registrado_em',
    ];

    public function encontro(): BelongsTo
    {
        return $this->belongsTo(TreinamentoEncontro::class, 'encontro_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(ColaboradorStw::class, 'pessoa_id_stw', 'pessoa_id_stw');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(ColaboradorStw::class, 'registrado_por_pessoa_id_stw', 'pessoa_id_stw');
    }
}
