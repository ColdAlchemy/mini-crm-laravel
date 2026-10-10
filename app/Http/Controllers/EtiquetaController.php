<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EtiquetaController extends Controller
{
    public function index()
    {
        return view('etiquetas.index', ['etiquetas' => Etiqueta::withCount('clientes')->orderBy('nome')->get()]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:40', 'unique:etiquetas,nome'],
            'cor' => ['required', Rule::in(array_keys(Etiqueta::CORES))],
        ], [
            'nome.required' => 'Dê um nome à etiqueta.',
            'nome.unique' => 'Já existe uma etiqueta com esse nome.',
            'cor.required' => 'Escolha uma cor.',
        ]);

        Etiqueta::create($dados);

        return redirect()->route('etiquetas.index')->with('ok', 'Etiqueta criada.');
    }

    public function destroy(Etiqueta $etiqueta)
    {
        $etiqueta->delete();

        return redirect()->route('etiquetas.index')->with('ok', 'Etiqueta removida. Os clientes continuam cadastrados.');
    }
}
