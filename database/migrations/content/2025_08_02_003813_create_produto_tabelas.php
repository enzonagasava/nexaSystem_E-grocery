<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
        Schema::connection('content')->create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('estoque')->default(0);
            $table->timestamps();

            // Adiciona índice para performance (opcional mas recomendado)
            $table->index('user_id', 'produtos_user_id_index');

            // Índice composto se for comum buscar por usuário + data
            $table->index(['user_id', 'created_at'], 'user_produtos_chrono_index');

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('content')->dropIfExists('produtos');
    }
};
