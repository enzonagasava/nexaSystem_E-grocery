<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePathNullableInImovelAutorizacoes extends Migration
{
    public function up()
    {
        Schema::table('imovel_autorizacoes', function (Blueprint $table) {
            if (Schema::hasColumn('imovel_autorizacoes', 'path')) {
                $table->string('path')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('imovel_autorizacoes', function (Blueprint $table) {
            if (Schema::hasColumn('imovel_autorizacoes', 'path')) {
                $table->string('path')->nullable(false)->change();
            }
        });
    }
}
