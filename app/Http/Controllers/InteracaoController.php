<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Interacao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InteracaoController extends Controller
{
    public function store(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(Interacao::TIPOS))],
            'descricao' => ['required', 'string', 'max:2000'],
        ], [
            'descricao.required' => 'Conte o que aconteceu neste contato.',
        ]);

        $cliente->interacoes()->create($dados);

        return back()->with('ok', 'Contato registrado.');
    }
}
