<?php
// database/migrations/2024_01_01_000002_create_kanban_columns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_colunas', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('kanban_quadro_id')->constrained('kanban_quadros')->onDelete('cascade');
            
            // Informações da coluna
            $table->foreignId('status_id')->constrained('status')->comment('status da coluna');
            $table->string('descricao')->nullable();
                        
            // Estilização
            $table->string('cor')->default('blue');
            $table->string('cor_fundo')->nullable();
            $table->string('icone')->nullable();
                        
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['kanban_quadro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_colunas');
    }
};