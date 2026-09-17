<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColaboradorStw extends Model
{
    protected $table = 'colaborador_stw';

    protected $fillable = [
        'pessoa_id_stw',
        'cpf',
        'nome',
        'empresa_id_stw',
        'perfil',
        'sincronizado_em',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresaStw::class, 'empresa_id_stw', 'id_stw');
    }

    public function treinamentosCriados(): HasMany
    {
        return $this->hasMany(Treinamento::class, 'criado_por_pessoa_id_stw', 'pessoa_id_stw');
    }

    public function atribuicoes(): HasMany
    {
        return $this->hasMany(AtribuicaoTreinamento::class, 'pessoa_id_stw', 'pessoa_id_stw');
    }

    public function atribuicoesAprovadas(): HasMany
    {
        return $this->hasMany(AtribuicaoTreinamento::class, 'aprovado_por_pessoa_id_stw', 'pessoa_id_stw');
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(PresencaTreinamento::class, 'pessoa_id_stw', 'pessoa_id_stw');
    }

    public function presencasRegistradas(): HasMany
    {
        return $this->hasMany(PresencaTreinamento::class, 'registrado_por_pessoa_id_stw', 'pessoa_id_stw');
    }
}
