<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\InteracaoController;
use App\Http\Controllers\NegocioController;
use App\Http\Controllers\PainelController;
use App\Http\Controllers\TarefaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [AuthController::class, 'formEntrar'])->name('entrar');
    Route::post('/entrar', [AuthController::class, 'entrar'])->middleware('throttle:6,1');
    Route::get('/cadastrar', [AuthController::class, 'formCadastrar'])->name('cadastrar');
    Route::post('/cadastrar', [AuthController::class, 'cadastrar'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/painel');
    Route::post('/sair', [AuthController::class, 'sair'])->name('sair');
    Route::get('/painel', [PainelController::class, 'index'])->name('painel');

    // Clientes (rotas fixas antes do {cliente})
    Route::get('clientes/importar', [ImportacaoController::class, 'form'])->name('clientes.importar');
    Route::post('clientes/importar', [ImportacaoController::class, 'importar'])->middleware('throttle:10,1');
    Route::get('clientes/modelo.csv', [ImportacaoController::class, 'modelo'])->name('clientes.modelo');
    Route::get('clientes.csv', [ClienteController::class, 'exportar'])->name('clientes.exportar');
    Route::resource('clientes', ClienteController::class);
    Route::put('clientes/{cliente}/etiquetas', [ClienteController::class, 'etiquetas'])->name('clientes.etiquetas');
    Route::post('clientes/{cliente}/interacoes', [InteracaoController::class, 'store'])->name('interacoes.store');

    // Funil
    Route::get('negocios', [NegocioController::class, 'index'])->name('negocios.index');
    Route::post('negocios', [NegocioController::class, 'store'])->name('negocios.store');
    Route::patch('negocios/{negocio}/etapa', [NegocioController::class, 'etapa'])->name('negocios.etapa');
    Route::delete('negocios/{negocio}', [NegocioController::class, 'destroy'])->name('negocios.destroy');

    // Retornos (tarefas)
    Route::get('tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
    Route::post('tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::patch('tarefas/{tarefa}/concluir', [TarefaController::class, 'concluir'])->name('tarefas.concluir');
    Route::patch('tarefas/{tarefa}/reabrir', [TarefaController::class, 'reabrir'])->name('tarefas.reabrir');
    Route::delete('tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tarefas.destroy');

    // Etiquetas
    Route::get('etiquetas', [EtiquetaController::class, 'index'])->name('etiquetas.index');
    Route::post('etiquetas', [EtiquetaController::class, 'store'])->name('etiquetas.store');
    Route::delete('etiquetas/{etiqueta}', [EtiquetaController::class, 'destroy'])->name('etiquetas.destroy');
});
