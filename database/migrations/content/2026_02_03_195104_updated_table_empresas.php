<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_painel_id')
                  ->nullable()
                  ->after('logo')
                  ->comment('Referência à tabela tipo_painel em outro banco');
            if (Schema::hasColumn('empresas', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
        
        // NOTA: Não podemos usar constrained() porque é outro banco
        // A validação será feita no nível da aplicação
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('tipo_painel_id');
        });
    }
};