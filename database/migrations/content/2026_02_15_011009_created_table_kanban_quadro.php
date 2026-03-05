<?php
// database/migrations/2024_01_01_000001_create_kanban_boards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_quadros', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->unsignedBigInteger('user_id');
            $table->json('permissao_users')->nullable();
            
            // Informações básicas
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->string('tipo')->default('personalizado'); // leads, contatos, imoveis, etc
            
            // Configurações
            $table->json('favoritos')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Ordem
            $table->integer('ordem')->default(0);
            $table->boolean('padrao')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_quadros');
    }
};