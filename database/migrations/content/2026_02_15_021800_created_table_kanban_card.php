<?php
// database/migrations/2024_01_01_000003_create_kanban_cards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_cards', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('kanban_column_id')->constrained('kanban_colunas')->onDelete('cascade');
            $table->foreignId('entidade_id'); // ID do lead, contato, etc
            $table->string('entidade_type'); // Lead::class, Contato::class, etc
            
            // Ordem dentro da coluna
            $table->integer('ordem')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['kanban_column_id', 'ordem']);
            $table->index(['entidade_id', 'entidade_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_cards');
    }
};