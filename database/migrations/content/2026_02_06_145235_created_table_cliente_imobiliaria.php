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
        Schema::create('cliente_imobiliaria', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->string('email')->unique()->nullable();
            $table->json('contatos')->nullable();
            $table->string('genero')->nullable();
            $table->date('data_nascimento')->nullable();
            
            $table->foreignId('rede_social_id')
                  ->nullable()
                  ->constrained('redes_sociais_leads')
                  ->onDelete('cascade');
            
            // Documentos
            $table->string('cpf', 14)->unique()->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('cnh', 20)->nullable();

            $table->decimal('renda_mensal', 10, 2)->nullable();
            $table->decimal('renda_familiar', 10, 2)->nullable();
            $table->enum('status_cliente', ['lead', 'contato'])->default('lead');
            $table->foreignId('status_id')->nullable()->constrained('status')->nullOnDelete();

            $table->string('tipo_relacao')->nullable()->comment('vendedor, comprador, inquilino e etc');
            $table->integer('nivel_interesse')->nullable()->comment('1 a 5'); 
            $table->date('ultimo_contato')->nullable();
            $table->json('preferencias_imoveis')->nullable(); // tipo, faixa_preco, etc
            $table->text('observacoes')->nullable();
            
            // Endereço
            $table->string('cep', 9)->nullable();
            $table->string('rua')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('complemento')->nullable();
            $table->integer('numero')->nullable();
            
            // Conta Bancária
            $table->string('banco_nome')->nullable();
            $table->string('banco_codigo', 3)->nullable();
            $table->string('agencia', 10)->unique()->nullable();
            $table->string('conta', 20)->unique()->nullable();
            $table->string('conta_tipo')->nullable();
            $table->string('pix')->nullable();
            $table->string('pix_tipo')->nullable();
            
            $table->unsignedBigInteger('corretor_id')->nullable();
            $table->unsignedBigInteger('imovel_id')->nullable();
            
            // Controle de rodízio e contato
            $table->boolean('adicionar_rodizio')->default(true);
            
            // Timestamps e soft deletes para inativação
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('status_id');
            $table->index('corretor_id');
            $table->index('adicionar_rodizio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_imobiliaria');
    }
};