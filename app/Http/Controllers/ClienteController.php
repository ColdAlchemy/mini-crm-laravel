<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->q, fn ($q, $busca) => $q->where(fn ($w) => $w
                ->where('nome', 'like', "%{$busca}%")
                ->orWhere('email', 'like', "%{$busca}%")
                ->orWhere('empresa', 'like', "%{$busca}%")))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente]);
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($this->validar($request));

        return redirect()->route('clientes.show', $cliente)->with('ok', 'Cliente cadastrado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['negocios', 'interacoes']);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($this->validar($request, $cliente));

        return redirect()->route('clientes.show', $cliente)->with('ok', 'Cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('ok', 'Cliente removido.');
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', Rule::unique('clientes', 'email')->ignore($cliente)],
            'telefone' => ['nullable', 'string', 'max:30'],
            'empresa' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(array_keys(Cliente::STATUS))],
        ]);
    }
}
