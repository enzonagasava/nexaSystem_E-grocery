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
        if (!Schema::hasTable('tamanhos')) {
            Schema::connection('content')->create('tamanhos', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('content')->dropIfExists('tamanhos');
    }
};
