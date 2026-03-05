<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('numero'); // CPF/CNPJ ou telefone?
            $table->string('endereco')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero_endereco')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();

            // Índices para performance (o Laravel gera nomes automaticamente)
            $table->index('nome');
            $table->index('email');
            $table->index('numero');
            $table->index('municipio');
            $table->index('estado');

            // Constraints de unicidade
            $table->unique('email');
            $table->unique('numero');

            // Índices compostos
            $table->index(['estado', 'municipio']);
            $table->index(['nome', 'email']);
        });

        // ⭐ CORREÇÃO PARA POSTGRESQL - Índices de texto completo
        DB::statement("
            CREATE INDEX idx_cliente_search_gin ON clientes 
            USING gin(to_tsvector('portuguese', nome || ' ' || email))
        ");

        DB::statement("
            CREATE INDEX idx_nome_gin ON clientes 
            USING gin(to_tsvector('portuguese', nome))
        ");
    }

    public function down(): void
    {
        // Remove índices GIN primeiro
        DB::statement('DROP INDEX IF EXISTS idx_cliente_search_gin');
        DB::statement('DROP INDEX IF EXISTS idx_nome_gin');
        
        Schema::dropIfExists('clientes');
    }
};