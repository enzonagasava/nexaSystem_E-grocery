<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
<<<<<<< HEAD
        Schema::create('whatsapp_mensagens', function (Blueprint $table) {
=======
        Schema::connection('content')->create('whatsapp_mensagens', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
<<<<<<< HEAD
        Schema::dropIfExists('whatsapp_mensagens');
=======
        Schema::connection('content')->dropIfExists('whatsapp_mensagens');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
