<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProprietarioColsToImovelAutorizacoes extends Migration
{
    public function up()
    {
        Schema::table('imovel_autorizacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('imovel_autorizacoes', 'proprietario_nome')) {
                $table->string('proprietario_nome')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('imovel_autorizacoes', 'proprietario_telefone')) {
                $table->string('proprietario_telefone')->nullable()->after('proprietario_nome');
            }
            if (!Schema::hasColumn('imovel_autorizacoes', 'proprietario_email')) {
                $table->string('proprietario_email')->nullable()->after('proprietario_telefone');
            }
            if (!Schema::hasColumn('imovel_autorizacoes', 'proprietario_documento')) {
                $table->string('proprietario_documento')->nullable()->after('proprietario_email');
            }
        });
    }

    public function down()
    {
        Schema::table('imovel_autorizacoes', function (Blueprint $table) {
            if (Schema::hasColumn('imovel_autorizacoes', 'proprietario_documento')) {
                $table->dropColumn('proprietario_documento');
            }
            if (Schema::hasColumn('imovel_autorizacoes', 'proprietario_email')) {
                $table->dropColumn('proprietario_email');
            }
            if (Schema::hasColumn('imovel_autorizacoes', 'proprietario_telefone')) {
                $table->dropColumn('proprietario_telefone');
            }
            if (Schema::hasColumn('imovel_autorizacoes', 'proprietario_nome')) {
                $table->dropColumn('proprietario_nome');
            }
        });
    }
}
