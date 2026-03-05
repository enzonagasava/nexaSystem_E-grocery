<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('integracoes_pagamento', function (Blueprint $table) {
=======
        Schema::connection('credentials')->create('integracoes_pagamento', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();

            // Campos para armazenar chaves criptografadas
            $table->text('public_key_encrypted')->nullable(); // Criptografada
            $table->text('access_key_encrypted')->nullable(); // Criptografada

            // Metadados
            $table->string('gateway')->default('mercadopago');
            $table->string('ambiente')->default('sandbox');
            $table->boolean('ativo')->default(true);

            // Usuário que configurou (opcional)
            $table->unsignedBigInteger('configurado_por')->nullable()->index();

            $table->timestamps();
            $table->softDeletes(); // Para desativação segura

            // Índices
            $table->index(['empresa_id', 'ativo'], 'empresa_integracao_ativa');
            $table->index('gateway', 'gateway_index');
        });
    }

    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('integracoes_pagamento');
=======
        Schema::connection('credentials')->dropIfExists('integracoes_pagamento');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
