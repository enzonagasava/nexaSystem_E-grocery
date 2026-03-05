<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('numero'); // CPF/CNPJ ou telefone?
=======
        Schema::connection('content')->create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('numero');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->string('endereco')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero_endereco')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado')->nullable();
            $table->timestamps();

<<<<<<< HEAD
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
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }

    public function down(): void
    {
<<<<<<< HEAD
        // Remove índices GIN primeiro
        DB::statement('DROP INDEX IF EXISTS idx_cliente_search_gin');
        DB::statement('DROP INDEX IF EXISTS idx_nome_gin');
        
        Schema::dropIfExists('clientes');
    }
};
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
