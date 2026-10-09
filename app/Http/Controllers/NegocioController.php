<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NegocioController extends Controller
{
    /** Quadro kanban: negócios agrupados por etapa. */
    public function index()
    {
        $negocios = Negocio::with('cliente')->latest()->get()->groupBy('etapa');
        $clientes = Cliente::orderBy('nome')->get(['id', 'nome']);

        return view('negocios.index', compact('negocios', 'clientes'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'valor' => ['required', 'numeric', 'min:0'],
        ]);

        Negocio::create($dados);

        return back()->with('ok', 'Negócio criado.');
    }

    public function etapa(Request $request, Negocio $negocio)
    {
        $dados = $request->validate(['etapa' => ['required', Rule::in(array_keys(Negocio::ETAPAS))]]);
        $negocio->update($dados);

        return back()->with('ok', 'Etapa atualizada.');
    }

    public function destroy(Negocio $negocio)
    {
        $negocio->delete();

        return back()->with('ok', 'Negócio removido.');
    }
}
