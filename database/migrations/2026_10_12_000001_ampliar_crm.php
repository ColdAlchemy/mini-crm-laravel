<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quando o negócio foi ganho ou perdido (base do gráfico mensal e da taxa de fechamento).
        Schema::table('negocios', function (Blueprint $table) {
            $table->timestamp('fechado_em')->nullable()->index();
        });

        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->date('vence_em')->index();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
        });

        Schema::create('etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('cor')->default('petroleo');
            $table->timestamps();
        });

        Schema::create('cliente_etiqueta', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('etiqueta_id')->constrained()->cascadeOnDelete();
            $table->primary(['cliente_id', 'etiqueta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_etiqueta');
        Schema::dropIfExists('etiquetas');
        Schema::dropIfExists('tarefas');
        Schema::table('negocios', fn (Blueprint $t) => $t->dropColumn('fechado_em'));
    }
};
