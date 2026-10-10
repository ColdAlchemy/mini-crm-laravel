<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    use HasFactory;

    /** Cores disponíveis: o nome vira classe CSS (.etiqueta--coral etc.). */
    public const CORES = [
        'petroleo' => 'Verde-petróleo',
        'coral' => 'Coral',
        'ambar' => 'Âmbar',
        'azul' => 'Azul',
        'violeta' => 'Violeta',
        'grafite' => 'Grafite',
    ];

    protected $fillable = ['nome', 'cor'];

    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class);
    }
}
