<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        // Tabela de respostas rápidas
<<<<<<< HEAD
        Schema::create('respostas_rapidas', function (Blueprint $table) {
=======
        Schema::connection('content')->create('respostas_rapidas', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->string('atalho', 50); // Ex: /bomdia
            $table->text('mensagem');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Tabela de configurações da IA
<<<<<<< HEAD
        Schema::create('configuracoes_ia', function (Blueprint $table) {
=======
        Schema::connection('content')->create('configuracoes_ia', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
<<<<<<< HEAD
        Schema::create('historico_mensagens', function (Blueprint $table) {
=======
        Schema::connection('content')->create('historico_mensagens', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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

    }

    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('historico_mensagens');
        Schema::dropIfExists('configuracoes_ia');
        Schema::dropIfExists('respostas_rapidas');
=======
        Schema::connection('content')->dropIfExists('historico_mensagens');
        Schema::connection('content')->dropIfExists('configuracoes_ia');
        Schema::connection('content')->dropIfExists('respostas_rapidas');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
