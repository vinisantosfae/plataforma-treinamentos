<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreinamentoPrerequisito extends Model
{
    protected $table = 'treinamento_prerequisito';

    public $timestamps = false;

    protected $fillable = [
        'treinamento_id',
        'prerequisito_treinamento_id',
    ];

    public function treinamento(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'treinamento_id');
    }

    public function prerequisito(): BelongsTo
    {
        return $this->belongsTo(Treinamento::class, 'prerequisito_treinamento_id');
    }
}
