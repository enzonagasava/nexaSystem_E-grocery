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
<<<<<<< HEAD
        Schema::create('produto_imagens', function (Blueprint $table) {
=======
        Schema::connection('content')->create('produto_imagens', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('imagem_path'); // caminho da imagem
            $table->integer('ordem')->default(0); // ordem da imagem no anúncio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('produto_imagens');
=======
        Schema::connection('content')->dropIfExists('produto_imagens');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
