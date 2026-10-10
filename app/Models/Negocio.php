<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Negocio extends Model
{
    use HasFactory;

    public const ETAPAS = [
        'novo' => 'Novo',
        'proposta' => 'Proposta',
        'negociacao' => 'Negociação',
        'ganho' => 'Ganho',
        'perdido' => 'Perdido',
    ];

    /** Etapas em que o negócio ainda está em andamento. */
    public const ABERTAS = ['novo', 'proposta', 'negociacao'];

    protected $fillable = ['cliente_id', 'titulo', 'valor', 'etapa', 'fechado_em'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2', 'fechado_em' => 'datetime'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Muda a etapa e marca (ou limpa) a data de fechamento. */
    public function moverPara(string $etapa): void
    {
        $fechou = in_array($etapa, ['ganho', 'perdido'], true);

        $this->update([
            'etapa' => $etapa,
            'fechado_em' => $fechou ? ($this->fechado_em && in_array($this->etapa, ['ganho', 'perdido'], true) ? $this->fechado_em : now()) : null,
        ]);
    }
}
