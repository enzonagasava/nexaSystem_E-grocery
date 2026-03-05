<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->date('data');
            $table->time('hora');
            $table->integer('duracao_minutos')->default(30);
            $table->string('tipo', 100)->default('consulta');
            $table->enum('status', ['pendente', 'confirmado', 'cancelado', 'realizado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'data']);
            $table->index(['empresa_id', 'paciente_id']);
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
