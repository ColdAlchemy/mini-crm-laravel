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

    protected $fillable = ['cliente_id', 'titulo', 'valor', 'etapa'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
