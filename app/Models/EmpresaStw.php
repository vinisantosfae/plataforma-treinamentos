<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaStw extends Model
{
    protected $table = 'empresa_stw';

    protected $fillable = [
        'id_stw',
        'razao_social',
        'sincronizado_em',
    ];

    public function colaboradores(): HasMany
    {
        return $this->hasMany(ColaboradorStw::class, 'empresa_id_stw', 'id_stw');
    }
}
