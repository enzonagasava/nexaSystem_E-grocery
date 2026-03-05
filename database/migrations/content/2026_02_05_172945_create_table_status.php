<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_status_table.php

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
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            
            // Identificação do status
            $table->string('nome');
            $table->text('descricao')->nullable();
                    
            // Ordenação
            $table->integer('ordem')->default(0);
                        
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};