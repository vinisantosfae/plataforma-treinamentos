<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoEncontro extends Model
{
    protected $table = 'treinamento_encontro';

    protected $fillable = [
        'treinamento_id',
        'data_inicio',
        'data_fim',
    ];

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'treinamento_id');
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(PresencaTreinamento::class, 'encontro_id');
    }
}
