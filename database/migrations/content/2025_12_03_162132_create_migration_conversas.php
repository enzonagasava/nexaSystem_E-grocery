<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('whatsapp_conversas', function (Blueprint $table) {
=======
        Schema::connection('content')->create('whatsapp_conversas', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->foreignId('contato_id')->constrained('whatsapp_contatos')->onDelete('cascade');
            $table->enum('status'   , ['aberto', 'fechado'])->default('aberto');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('whatsapp_conversas');
=======
        Schema::connection('content')->dropIfExists('whatsapp_conversas');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
