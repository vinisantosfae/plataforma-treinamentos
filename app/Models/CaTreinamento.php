<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaTreinamento extends Model
{
    protected $table = 'ca_treinamento';

    protected $fillable = [
        'produto_id_stw',
        'treinamento_id',
    ];

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CaStw::class, 'produto_id_stw', 'produto_id_stw');
    }

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'treinamento_id');
    }
}
