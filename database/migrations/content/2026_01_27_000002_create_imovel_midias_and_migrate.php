<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateImovelMidiasAndMigrate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imovel_midias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('imovel_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo'); // planta | video | autorizacao
            $table->string('path');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // migrate existing data from imoveis table into imovel_midias
        $rows = DB::table('imoveis')->select('id','planta_path','video_path','autorizacao_path','user_id')->get();
        foreach ($rows as $row) {
            if ($row->planta_path) {
                DB::table('imovel_midias')->insert([
                    'imovel_id' => $row->id,
                    'user_id' => $row->user_id,
                    'tipo' => 'planta',
                    'path' => $row->planta_path,
                    'ordem' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if ($row->video_path) {
                DB::table('imovel_midias')->insert([
                    'imovel_id' => $row->id,
                    'user_id' => $row->user_id,
                    'tipo' => 'video',
                    'path' => $row->video_path,
                    'ordem' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if ($row->autorizacao_path) {
                DB::table('imovel_midias')->insert([
                    'imovel_id' => $row->id,
                    'user_id' => $row->user_id,
                    'tipo' => 'autorizacao',
                    'path' => $row->autorizacao_path,
                    'ordem' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // drop the columns from imoveis
        Schema::table('imoveis', function (Blueprint $table) {
            if (Schema::hasColumn('imoveis', 'planta_path')) {
                $table->dropColumn('planta_path');
            }
            if (Schema::hasColumn('imoveis', 'video_path')) {
                $table->dropColumn('video_path');
            }
            if (Schema::hasColumn('imoveis', 'autorizacao_path')) {
                $table->dropColumn('autorizacao_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // re-add columns to imoveis
        Schema::table('imoveis', function (Blueprint $table) {
            $table->string('planta_path')->nullable()->after('itens');
            $table->string('video_path')->nullable()->after('planta_path');
            $table->string('autorizacao_path')->nullable()->after('video_path');
        });

        // move data back from imovel_midias into imoveis where appropriate
        $midias = DB::table('imovel_midias')->get();
        foreach ($midias as $m) {
            if ($m->tipo === 'planta') {
                DB::table('imoveis')->where('id', $m->imovel_id)->update(['planta_path' => $m->path]);
            }
            if ($m->tipo === 'video') {
                DB::table('imoveis')->where('id', $m->imovel_id)->update(['video_path' => $m->path]);
            }
            if ($m->tipo === 'autorizacao') {
                DB::table('imoveis')->where('id', $m->imovel_id)->update(['autorizacao_path' => $m->path]);
            }
        }

        Schema::dropIfExists('imovel_midias');
    }
}
