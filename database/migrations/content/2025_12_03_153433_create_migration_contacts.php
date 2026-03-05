<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('whatsapp_contatos', function (Blueprint $table) {
=======
        Schema::connection('content')->create('whatsapp_contatos', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->string('numero', 20)->unique();
            $table->string('nome')->nullable();
            $table->string('foto_perfil')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('whatsapp_contatos');
=======
        Schema::connection('content')->dropIfExists('whatsapp_contatos');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
