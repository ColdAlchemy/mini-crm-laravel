<?php

namespace App\Http\Controllers;

use App\Services\Painel;

class PainelController extends Controller
{
    public function index(Painel $painel)
    {
        return view('painel', [
            'ind' => $painel->indicadores(),
            'funil' => $painel->funil(),
            'meses' => $painel->ganhosPorMes(),
            'retornos' => $painel->retornos(),
            'semContato' => $painel->semContato(),
        ]);
    }
}
