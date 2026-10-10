<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Support\Formato;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NegocioController extends Controller
{
    /** Funil em quadro kanban: negócios agrupados por etapa. */
    public function index(Request $request)
    {
        $negocios = Negocio::with('cliente:id,nome,telefone')->latest()->get()->groupBy('etapa');

        return view('negocios.index', [
            'negocios' => $negocios,
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'clienteEscolhido' => (int) $request->query('cliente'),
        ]);
    }

    public function store(Request $request)
    {
        // "1.500,50" e "R$ 1500" também valem.
        $request->merge(['valor' => Formato::decimal($request->input('valor'))]);

        $dados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'valor' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ], [
            'cliente_id.required' => 'Escolha o cliente do negócio.',
            'titulo.required' => 'Dê um nome ao negócio. Ex.: Reforma da vitrine.',
            'valor.required' => 'Informe o valor, mesmo que seja uma estimativa.',
            'valor.numeric' => 'O valor precisa ser um número. Ex.: 1500,00.',
        ]);

        Negocio::create($dados);

        return back()->with('ok', 'Negócio criado.');
    }

    /** Muda a etapa. Responde JSON para o arrastar e soltar; sem JavaScript, volta para a página. */
    public function etapa(Request $request, Negocio $negocio)
    {
        $dados = $request->validate(['etapa' => ['required', Rule::in(array_keys(Negocio::ETAPAS))]]);
        $negocio->moverPara($dados['etapa']);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $negocio->id,
                'etapa' => $negocio->etapa,
                'rotulo' => Negocio::ETAPAS[$negocio->etapa],
                'fechado_em' => $negocio->fechado_em?->toIso8601String(),
            ]);
        }

        return back()->with('ok', "“{$negocio->titulo}” foi para ".Negocio::ETAPAS[$negocio->etapa].'.');
    }

    public function destroy(Negocio $negocio)
    {
        $negocio->delete();

        return back()->with('ok', 'Negócio removido.');
    }
}
