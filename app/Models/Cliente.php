<?php

namespace App\Models;

use App\Support\Formato;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class)->orderBy('vence_em');
    }

    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(Etiqueta::class)->orderBy('etiquetas.nome');
    }

    /** Só os dígitos do telefone, ou null se não houver. */
    public function telefoneDigitos(): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $this->telefone);

        return $digitos === '' ? null : $digitos;
    }

    /**
     * Link "chamar no WhatsApp" (wa.me). Aceita números brasileiros com ou sem o 55;
     * devolve null quando o telefone não parece um número válido (DDD + 8 ou 9 dígitos).
     */
    public function linkWhatsapp(?string $mensagem = null): ?string
    {
        $numero = $this->telefoneDigitos();
        if ($numero === null) {
            return null;
        }
        if (str_starts_with($numero, '55') && in_array(strlen($numero), [12, 13], true)) {
            $numero = substr($numero, 2);
        }
        if (! in_array(strlen($numero), [10, 11], true)) {
            return null;
        }

        $url = 'https://wa.me/55'.$numero;
        $mensagem ??= 'Olá, '.explode(' ', trim($this->nome))[0].'! Tudo bem?';

        return $url.'?text='.rawurlencode($mensagem);
    }

    /**
     * Linha do tempo: interações, negócios e tarefas concluídas, do mais recente para o mais antigo.
     *
     * @return Collection<int, array{quando: Carbon, tipo: string, titulo: string, detalhe: ?string}>
     */
    public function linhaDoTempo(): Collection
    {
        $eventos = collect();

        foreach ($this->interacoes as $i) {
            $eventos->push(['quando' => $i->created_at, 'tipo' => $i->tipo, 'titulo' => Interacao::TIPOS[$i->tipo] ?? $i->tipo, 'detalhe' => $i->descricao]);
        }
        foreach ($this->negocios as $n) {
            $eventos->push(['quando' => $n->created_at, 'tipo' => 'negocio', 'titulo' => 'Negócio aberto: '.$n->titulo, 'detalhe' => Formato::moeda($n->valor)]);
            if ($n->fechado_em) {
                $eventos->push([
                    'quando' => $n->fechado_em,
                    'tipo' => $n->etapa === 'ganho' ? 'ganho' : 'perdido',
                    'titulo' => ($n->etapa === 'ganho' ? 'Negócio ganho: ' : 'Negócio perdido: ').$n->titulo,
                    'detalhe' => Formato::moeda($n->valor),
                ]);
            }
        }
        foreach ($this->tarefas as $t) {
            if ($t->concluida_em) {
                $eventos->push(['quando' => $t->concluida_em, 'tipo' => 'tarefa', 'titulo' => 'Retorno concluído: '.$t->titulo, 'detalhe' => null]);
            }
        }

        return $eventos->sortByDesc('quando')->values();
    }
}
