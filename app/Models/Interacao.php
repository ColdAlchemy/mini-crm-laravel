<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interacao extends Model
{
    public const TIPOS = ['ligacao' => 'Ligação', 'email' => 'E-mail', 'reuniao' => 'Reunião', 'whatsapp' => 'WhatsApp', 'nota' => 'Nota'];

    protected $table = 'interacoes';

    protected $fillable = ['cliente_id', 'tipo', 'descricao'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
