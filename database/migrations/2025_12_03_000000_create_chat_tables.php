<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de respostas rápidas
        Schema::create('respostas_rapidas', function (Blueprint $table) {
            $table->id();
            $table->string('atalho', 50); // Ex: /bomdia
            $table->text('mensagem');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Tabela de configurações da IA
        Schema::create('configuracoes_ia', function (Blueprint $table) {
            $table->id();
            $table->boolean('bot_ativo')->default(false);
            $table->enum('tom_voz', ['amigavel', 'profissional'])->default('amigavel');
            $table->text('mensagem_boas_vindas')->nullable();
            $table->text('mensagem_fora_horario')->nullable();
            $table->integer('timer_ausencia')->default(300); // segundos (5 min)
            $table->boolean('bloquear_bot')->default(false);
            $table->timestamp('bloqueio_ate')->nullable();
            $table->timestamps();
        });

        // Tabela de histórico de mensagens
        Schema::create('historico_mensagens', function (Blueprint $table) {
            $table->id();
            $table->string('remote_jid');
            $table->string('message_id');
            $table->text('conteudo');
            $table->boolean('de_mim')->default(false);
            $table->boolean('processado_ia')->default(false);
            $table->timestamp('enviado_em');
            $table->timestamps();
            
            $table->index(['remote_jid', 'enviado_em']);
        });

        // Inserir configuração padrão
        DB::table('configuracoes_ia')->insert([
            'bot_ativo' => false,
            'tom_voz' => 'amigavel',
            'mensagem_boas_vindas' => 'Olá! Seja bem-vindo(a)! Como posso ajudar?',
            'mensagem_fora_horario' => 'Obrigado por entrar em contato! No momento estamos fora do horário de atendimento. Retornaremos em breve.',
            'timer_ausencia' => 300,
            'bloquear_bot' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_mensagens');
        Schema::dropIfExists('configuracoes_ia');
        Schema::dropIfExists('respostas_rapidas');
    }
};
