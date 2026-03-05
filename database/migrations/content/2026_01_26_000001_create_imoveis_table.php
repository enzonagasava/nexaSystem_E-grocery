<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImoveisTable extends Migration
{
    public function up()
    {
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('codigo')->nullable();
            $table->string('nome')->nullable();
            $table->text('descricao')->nullable();

            // Status, categoria, finalidade
            $table->string('status')->default('ativo');
            $table->string('categoria')->nullable();
            $table->string('finalidade')->default('venda');
            $table->boolean('exclusividade')->default(false);

            // Localização
            $table->string('cep')->nullable();
            $table->string('estado')->nullable();
            $table->string('cidade')->nullable();
            $table->string('bairro')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('referencia')->nullable();
            $table->boolean('mostrar_endereco_completo')->default(true);

            // Valores
            $table->decimal('valor_venda', 14, 2)->nullable();
            $table->decimal('valor_condominio', 12, 2)->nullable();
            $table->decimal('valor_iptu', 12, 2)->nullable();
            $table->boolean('aceita_financiamento')->default(false);
            $table->boolean('aceita_permuta')->default(false);
            $table->decimal('comissao_percent', 5, 2)->nullable();
            $table->decimal('comissao_valor', 12, 2)->nullable();

            // Características físicas
            $table->decimal('area_total', 10, 2)->nullable();
            $table->decimal('area_construida', 10, 2)->nullable();
            $table->unsignedSmallInteger('quartos')->nullable();
            $table->unsignedSmallInteger('suites')->nullable();
            $table->unsignedSmallInteger('banheiros')->nullable();
            $table->unsignedSmallInteger('vagas')->nullable();
            $table->string('andar')->nullable();
            $table->unsignedSmallInteger('ano_construcao')->nullable();

            // Mobília / itens
            $table->string('mobilia')->nullable();
            $table->json('itens')->nullable();

            // Proprietário
            $table->string('proprietario_nome')->nullable();
            $table->string('proprietario_telefone')->nullable();
            $table->string('proprietario_email')->nullable();
            $table->string('proprietario_documento')->nullable();
            $table->boolean('autorizacao_venda')->default(false);

            $table->timestamps();

            // NOTE: users table is created on a different connection ('credentials'),
            // so we cannot add a cross-connection foreign key constraint here.
            // Keep user_id indexed and enforce integrity at application level.
        });
    }

    public function down()
    {
        Schema::dropIfExists('imoveis');
    }
}
