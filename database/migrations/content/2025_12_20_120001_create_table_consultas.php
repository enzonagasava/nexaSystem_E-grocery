<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('content')->create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->date('data_consulta');
            $table->time('hora_inicio');
            $table->time('hora_fim')->nullable();
            $table->string('tipo', 100)->default('consulta');
            $table->enum('status', ['agendada', 'em-andamento', 'realizada', 'cancelada'])->default('agendada');
            $table->decimal('valor', 10, 2)->nullable();
            $table->text('motivo')->nullable();
            $table->text('observacoes')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('prescricao')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'data_consulta']);
            $table->index(['empresa_id', 'paciente_id']);
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('content')->dropIfExists('consultas');
    }
};
