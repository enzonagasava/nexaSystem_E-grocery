<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('agendamentos', function (Blueprint $table) {
=======
        Schema::connection('content')->create('agendamentos', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
<<<<<<< HEAD
        Schema::dropIfExists('agendamentos');
=======
        Schema::connection('content')->dropIfExists('agendamentos');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
