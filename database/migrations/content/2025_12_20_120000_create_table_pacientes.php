<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('content')->create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cpf', 14)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->enum('sexo', ['masculino', 'feminino', 'outro'])->nullable();
            $table->string('telefone', 20);
            $table->string('email')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero_endereco', 20)->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('convenio')->nullable();
            $table->string('numero_convenio')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'nome']);
            $table->index(['empresa_id', 'cpf']);
        });
    }

    public function down(): void
    {
        Schema::connection('content')->dropIfExists('pacientes');
    }
};
