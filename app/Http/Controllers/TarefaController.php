<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index()
    {
        $pendentes = Tarefa::with('cliente:id,nome,telefone')->whereNull('concluida_em');
        $hoje = now()->toDateString();

        return view('tarefas.index', [
            'atrasadas' => (clone $pendentes)->whereDate('vence_em', '<', $hoje)->orderBy('vence_em')->get(),
            'hoje' => (clone $pendentes)->whereDate('vence_em', $hoje)->orderBy('id')->get(),
            'proximas' => (clone $pendentes)->whereDate('vence_em', '>', $hoje)->orderBy('vence_em')->get(),
            'concluidas' => Tarefa::with('cliente:id,nome')->whereNotNull('concluida_em')->latest('concluida_em')->limit(10)->get(),
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'vence_em' => ['required', 'date'],
        ], [
            'cliente_id.required' => 'Escolha o cliente do retorno.',
            'titulo.required' => 'Diga o que precisa ser feito.',
            'vence_em.required' => 'Escolha o dia do retorno.',
            'vence_em.date' => 'Esse dia não parece válido.',
        ]);

        Tarefa::create($dados);

        return back()->with('ok', 'Retorno agendado.');
    }

    public function concluir(Tarefa $tarefa)
    {
        $tarefa->update(['concluida_em' => now()]);

        return back()->with('ok', 'Retorno concluído.');
    }

    public function reabrir(Tarefa $tarefa)
    {
        $tarefa->update(['concluida_em' => null]);

        return back()->with('ok', 'Retorno reaberto.');
    }

    public function destroy(Tarefa $tarefa)
    {
        $tarefa->delete();

        return back()->with('ok', 'Retorno removido.');
    }
}
