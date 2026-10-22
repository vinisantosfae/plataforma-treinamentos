<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = [
        'nome',
        'id_empresa',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresaStw::class, 'id_empresa', 'id_stw');
    }

    public function treinamentos(): BelongsToMany
    {
        return $this->belongsToMany(Treinamento::class, 'treinamento_grupos', 'grupo_id', 'treinamento_id');
    }
}
