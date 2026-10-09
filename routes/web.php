<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InteracaoController;
use App\Http\Controllers\NegocioController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/clientes');

Route::resource('clientes', ClienteController::class);
Route::post('clientes/{cliente}/interacoes', [InteracaoController::class, 'store'])->name('interacoes.store');

Route::get('negocios', [NegocioController::class, 'index'])->name('negocios.index');
Route::post('negocios', [NegocioController::class, 'store'])->name('negocios.store');
Route::patch('negocios/{negocio}/etapa', [NegocioController::class, 'etapa'])->name('negocios.etapa');
Route::delete('negocios/{negocio}', [NegocioController::class, 'destroy'])->name('negocios.destroy');
