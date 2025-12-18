<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('content')->create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('numero');
            $table->string('endereco')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero_endereco')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();

            // Índices para performance
            $table->index('nome', 'name_index');
            $table->index('email', 'email_index');
            $table->index('numero', 'numero_index');
            $table->index('municipio', 'municipio_index');
            $table->index('estado', 'estado_index');

            // Constraints de unicidade
            $table->unique('email', 'email_unique');
            $table->unique('numero', 'numero_unique');

            // Índices compostos
            $table->index(['estado', 'municipio'], 'estado_municipio_index');
            $table->index(['nome', 'email'], 'nome_email_index');
        });

        usleep(100000);

        DB::connection('content')->statement(
            'ALTER TABLE clientes ADD FULLTEXT INDEX ft_cliente_search (nome, email)'
        );

        DB::connection('content')->statement(
            'ALTER TABLE clientes ADD FULLTEXT INDEX ft_nome (nome)'
        );
    }

    public function down(): void
    {
        // ✅ ADICIONE PARA REMOVER FULLTEXT:
        try {
            DB::connection('content')->statement('ALTER TABLE clientes DROP INDEX ft_cliente_search');
        } catch (\Exception $e) {}

        try {
            DB::connection('content')->statement('ALTER TABLE clientes DROP INDEX ft_nome');
        } catch (\Exception $e) {}

        Schema::connection('content')->dropIfExists('clientes');
    }
};
