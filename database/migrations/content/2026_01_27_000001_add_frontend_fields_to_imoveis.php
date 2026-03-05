<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrontendFieldsToImoveis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imoveis', function (Blueprint $table) {
            $table->string('modalidade')->nullable()->after('status');
            $table->string('condicao')->nullable()->after('modalidade');

            $table->decimal('area_terreno_largura', 10, 2)->nullable()->after('area_total');
            $table->decimal('area_terreno_comprimento', 10, 2)->nullable()->after('area_terreno_largura');
            $table->decimal('area_util', 10, 2)->nullable()->after('area_construida');
            $table->integer('salas')->nullable()->after('area_util');
            $table->boolean('varanda')->default(false)->after('salas');
            $table->text('areas_lazer')->nullable()->after('varanda');

            $table->decimal('valor_locacao', 15, 2)->nullable()->after('valor_venda');
            $table->string('gas')->nullable()->after('valor_iptu');
            $table->string('luz')->nullable()->after('gas');

            // media paths
            $table->string('planta_path')->nullable()->after('itens');
            $table->string('video_path')->nullable()->after('planta_path');
            $table->string('autorizacao_path')->nullable()->after('video_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imoveis', function (Blueprint $table) {
            $table->dropColumn([
                'modalidade','condicao',
                'area_terreno_largura','area_terreno_comprimento','area_util','salas','varanda','areas_lazer',
                'valor_locacao','gas','luz',
                'planta_path','video_path','autorizacao_path'
            ]);
        });
    }
}
