<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tarefa de retorno: "ligar para o cliente até tal dia". */
class Tarefa extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'titulo', 'vence_em', 'concluida_em'];

    protected function casts(): array
    {
        return ['vence_em' => 'date', 'concluida_em' => 'datetime'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estaConcluida(): bool
    {
        return $this->concluida_em !== null;
    }

    /** Dias até o vencimento (negativo se atrasada), contados a partir de hoje. */
    public function diasRestantes(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->vence_em->copy()->startOfDay(), false);
    }
}
