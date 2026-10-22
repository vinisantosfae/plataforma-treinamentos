<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreinamentoGrupo extends Model
{
    protected $table = 'treinamento_grupos';

    protected $fillable = [
        'treinamento_id',
        'grupo_id',
    ];

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'treinamento_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }
}
