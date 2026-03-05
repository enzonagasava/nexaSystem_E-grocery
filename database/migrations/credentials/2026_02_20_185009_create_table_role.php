<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up (){

        // Tabela de módulos (funcionalidades)
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique(); // 'chat', 'agenda', 'imoveis', 'pacientes'
            $table->string('display_name');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        // Relacionamento painel_módulo (quais módulos cada painel tem)
        Schema::create('painel_modulo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('painel_id')->nullable();
            $table->foreignId('modulo_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['painel_id', 'modulo_id']);
        });


        // Tabela de permissões (agora com contexto)
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // 'visualizar', 'criar', 'editar'
            $table->string('recurso'); // 'chat', 'agenda', 'imovel', 'paciente'
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->timestamps();
            
            $table->unique(['nome', 'recurso', 'modulo_id']);
        });

        // Relacionamento cargo_permissao (agora com escopo)
        Schema::create('cargo_permissao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained()->onDelete('cascade');
            $table->foreignId('permissao_id')->constrained('permissoes')->onDelete('cascade');
            $table->unsignedBigInteger('painel_id')->nullable(); // escopo (tipo_painel.id em nexa_admin)
            $table->timestamps();
            
            $table->unique(['cargo_id', 'permissao_id', 'painel_id'], 'cargo_permissao_painel_unique');
        });

        // Pivot usuário ↔ tipo_painel (múltiplos painéis por usuário)
        Schema::create('painel_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('painel_id'); // tipo_painel.id em nexa_admin (sem FK)
            $table->json('configuracoes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'painel_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('painel_user');
        Schema::dropIfExists('cargo_permissao');
        Schema::dropIfExists('permissoes');
        Schema::dropIfExists('painel_modulo');
        Schema::dropIfExists('modulos');
    }
};
