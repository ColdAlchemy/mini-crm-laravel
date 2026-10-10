<?php

namespace App\Http\Controllers;

use App\Services\ImportadorCsv;
use Illuminate\Http\Request;
use RuntimeException;

class ImportacaoController extends Controller
{
    public function form()
    {
        return view('clientes.importar');
    }

    public function importar(Request $request, ImportadorCsv $importador)
    {
        $request->validate([
            'planilha' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
        ], [
            'planilha.required' => 'Escolha o arquivo CSV da planilha.',
            'planilha.mimes' => 'O arquivo precisa ser .csv. No Excel: Salvar como → CSV.',
            'planilha.max' => 'O arquivo pode ter até 1 MB.',
        ]);

        try {
            $resultado = $importador->importar($request->file('planilha')->getRealPath());
        } catch (RuntimeException $e) {
            return back()->withErrors(['planilha' => $e->getMessage()]);
        }

        return redirect()->route('clientes.importar')->with('resultado', $resultado);
    }

    /** Planilha de exemplo para a pessoa preencher. */
    public function modelo()
    {
        $csv = "\xEF\xBB\xBF"."nome;email;telefone;empresa;status\r\n"
            ."Maria Souza;maria@exemplo.com;(99) 98888-7777;Padaria Pão Quente;ativo\r\n"
            ."João Pereira;;(99) 97777-6666;;lead\r\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo-clientes.csv"',
        ]);
    }
}
