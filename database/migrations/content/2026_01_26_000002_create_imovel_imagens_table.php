<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImovelImagensTable extends Migration
{
    public function up()
    {
        Schema::create('imovel_imagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('imagem_path');
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imovel_imagens');
    }
}
