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
        if (!Schema::hasTable('produto_tamanho')) {
            Schema::connection('content')->create('produto_tamanho', function (Blueprint $table) {
                $table->foreignId('produto_id')->constrained()->onDelete('cascade');
                $table->foreignId('tamanho_id')->constrained()->onDelete('cascade');
                $table->decimal('preco', 8, 2);
                $table->primary(['produto_id', 'tamanho_id']);
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('content')->dropIfExists('produto_tamanho');
    }
};
