<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contato_id')->constrained('whatsapp_contatos')->onDelete('cascade');
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversas');
    }
};
