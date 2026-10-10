<?php

namespace App\Providers;

use App\Models\Tarefa;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Selo do menu: retornos de hoje ou atrasados (só consulta quando há alguém logado).
        View::composer('layout', function ($view) {
            $view->with('retornosPendentes', auth()->check()
                ? Tarefa::whereNull('concluida_em')->whereDate('vence_em', '<=', now()->toDateString())->count()
                : 0);
        });
    }
}
