<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treinamento extends Model
{
    protected $table = 'treinamento';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'video_url_youtube',
        'categoria',
        'prazo_dias',
        'presenca_minima_percentual',
        'duracao_video_segundos',
        'ativo',
        'id_empresa',
        'criado_por_pessoa_id_stw',
    ];

    public function criador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'criado_por_pessoa_id_stw', 'pessoa_id_stw');
    }

    public function encontros(): HasMany
    {
        return $this->hasMany(TreinamentoEncontro::class, 'treinamento_id');
    }

    public function atribuicoes(): HasMany
    {
        return $this->hasMany(AtribuicaoTreinamento::class, 'treinamento_id');
    }

    public function prerequisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'treinamento_prerequisito',
            'treinamento_id',
            'prerequisito_treinamento_id'
        );
    }

    public function requisitoDe(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'treinamento_prerequisito',
            'prerequisito_treinamento_id',
            'treinamento_id'
        );
    }

    public function cas(): BelongsToMany
    {
        return $this->belongsToMany(
            CaStw::class,
            'ca_treinamento',
            'treinamento_id',
            'produto_id_stw',
            'id',
            'produto_id_stw'
        );
    }
}
