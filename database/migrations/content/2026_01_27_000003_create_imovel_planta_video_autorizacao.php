<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateImovelPlantaVideoAutorizacao extends Migration
{
    public function up()
    {
        Schema::create('imovel_plantas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('imovel_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('imovel_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('imovel_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('imovel_autorizacoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('imovel_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('path');
            $table->timestamps();
        });

        // migrate data from imovel_midias if present
        if (Schema::hasTable('imovel_midias')) {
            $midias = DB::table('imovel_midias')->get();
            foreach ($midias as $m) {
                if ($m->tipo === 'planta') {
                    DB::table('imovel_plantas')->insert([
                        'imovel_id' => $m->imovel_id,
                        'user_id' => $m->user_id,
                        'path' => $m->path,
                        'created_at' => $m->created_at,
                        'updated_at' => $m->updated_at,
                    ]);
                }
                if ($m->tipo === 'video') {
                    DB::table('imovel_videos')->insert([
                        'imovel_id' => $m->imovel_id,
                        'user_id' => $m->user_id,
                        'path' => $m->path,
                        'created_at' => $m->created_at,
                        'updated_at' => $m->updated_at,
                    ]);
                }
                if ($m->tipo === 'autorizacao') {
                    DB::table('imovel_autorizacoes')->insert([
                        'imovel_id' => $m->imovel_id,
                        'user_id' => $m->user_id,
                        'path' => $m->path,
                        'created_at' => $m->created_at,
                        'updated_at' => $m->updated_at,
                    ]);
                }
            }
            // optionally drop midias table
            Schema::dropIfExists('imovel_midias');
        }
    }

    public function down()
    {
        Schema::dropIfExists('imovel_plantas');
        Schema::dropIfExists('imovel_videos');
        Schema::dropIfExists('imovel_autorizacoes');
    }
}
