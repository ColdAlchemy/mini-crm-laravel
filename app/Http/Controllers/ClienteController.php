<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Etiqueta;
use App\Models\Negocio;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $this->filtros($request);

        $clientes = $this->consulta($filtros)
            ->with('etiquetas')
            ->withMax('interacoes as ultimo_contato', 'created_at')
            ->withMin(['tarefas as proximo_retorno' => fn ($q) => $q->whereNull('concluida_em')], 'vence_em')
            ->when(
                $filtros['ordem'] === 'contato',
                fn ($q) => $q->orderByRaw('ultimo_contato is null desc, ultimo_contato asc'),
                fn ($q) => $q->orderBy('nome'),
            )
            ->paginate(12)
            ->withQueryString();

        return view('clientes.index', [
            'clientes' => $clientes,
            'filtros' => $filtros,
            'etiquetas' => Etiqueta::orderBy('nome')->get(),
            'total' => Cliente::count(),
        ]);
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente(['status' => 'lead'])]);
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($this->validar($request));

        return redirect()->route('clientes.show', $cliente)->with('ok', 'Cliente cadastrado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['negocios', 'interacoes', 'tarefas', 'etiquetas']);

        return view('clientes.show', [
            'cliente' => $cliente,
            'linhaDoTempo' => $cliente->linhaDoTempo(),
            'todasEtiquetas' => Etiqueta::orderBy('nome')->get(),
            'etapas' => Negocio::ETAPAS,
        ]);
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

    public function etiquetas(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['integer', 'exists:etiquetas,id'],
        ]);

        $cliente->etiquetas()->sync($dados['etiquetas'] ?? []);

        return back()->with('ok', 'Etiquetas atualizadas.');
    }

    /** Baixa os clientes (com os filtros atuais) em CSV que o Excel abre direto. */
    public function exportar(Request $request): StreamedResponse
    {
        $consulta = $this->consulta($this->filtros($request))->with('etiquetas')->orderBy('nome');

        return response()->streamDownload(function () use ($consulta) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF"); // BOM: o Excel reconhece os acentos
            fputcsv($saida, ['nome', 'email', 'telefone', 'empresa', 'status', 'etiquetas'], ';');
            $consulta->chunk(200, function ($lote) use ($saida) {
                foreach ($lote as $c) {
                    fputcsv($saida, [
                        $this->protegerPlanilha($c->nome), $c->email, $this->protegerPlanilha($c->telefone),
                        $this->protegerPlanilha($c->empresa), $c->status, $this->protegerPlanilha($c->etiquetas->pluck('nome')->implode(', ')),
                    ], ';');
                }
            });
            fclose($saida);
        }, 'clientes-balcao.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita que textos como "=SOMA(...)" virem fórmula quando a planilha for aberta no Excel. */
    private function protegerPlanilha(?string $texto): ?string
    {
        return $texto !== null && preg_match('/^[=+\-@\t\r]/', $texto) ? "'".$texto : $texto;
    }

    /** @return array{q: ?string, status: ?string, etiqueta: ?int, ordem: string} */
    private function filtros(Request $request): array
    {
        $dados = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Cliente::STATUS))],
            'etiqueta' => ['nullable', 'integer'],
            'ordem' => ['nullable', Rule::in(['nome', 'contato'])],
        ]);

        return [
            'q' => $dados['q'] ?? null,
            'status' => $dados['status'] ?? null,
            'etiqueta' => isset($dados['etiqueta']) ? (int) $dados['etiqueta'] : null,
            'ordem' => $dados['ordem'] ?? 'nome',
        ];
    }

    private function consulta(array $filtros): Builder
    {
        return Cliente::query()
            ->when($filtros['q'], function ($q, $busca) {
                $busca = str_replace(['%', '_'], ['\%', '\_'], $busca);

                return $q->where(fn ($w) => $w
                    ->where('nome', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%")
                    ->orWhere('empresa', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%"));
            })
            ->when($filtros['status'], fn ($q, $status) => $q->where('status', $status))
            ->when($filtros['etiqueta'], fn ($q, $id) => $q->whereHas('etiquetas', fn ($e) => $e->where('etiquetas.id', $id)));
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', Rule::unique('clientes', 'email')->ignore($cliente)],
            'telefone' => ['nullable', 'string', 'max:30'],
            'empresa' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(array_keys(Cliente::STATUS))],
        ], [
            'nome.required' => 'Informe o nome do cliente.',
            'email.email' => 'Esse e-mail não parece válido. Confira se não falta o @.',
            'email.unique' => 'Já existe um cliente com esse e-mail.',
        ]);
    }
}
