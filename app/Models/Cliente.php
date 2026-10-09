<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    public const STATUS = ['lead' => 'Lead', 'ativo' => 'Ativo', 'inativo' => 'Inativo'];

    protected $fillable = ['nome', 'email', 'telefone', 'empresa', 'status'];

    public function negocios(): HasMany
    {
        return $this->hasMany(Negocio::class);
    }

    public function interacoes(): HasMany
    {
        return $this->hasMany(Interacao::class)->latest();
    }
}
