<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redes_sociais_leads', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('lead_id')->nullable()
                  ->comment('Referência ao lead/cliente');
            
            // Redes sociais principais
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('x')->nullable()->comment('Twitter/X');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redes_sociais_leads');
    }
};