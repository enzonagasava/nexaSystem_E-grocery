<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_mensagens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversas_id')
                ->constrained('whatsapp_contatos')
                ->onDelete('cascade');

            $table->enum('direction', ['incoming', 'outgoing']);
            $table->string('type')->default('text');

            $table->longText('body')->nullable();
            $table->string('wamid')->nullable()->index();

            $table->boolean('sent_by_human')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_mensagens');
    }
};
