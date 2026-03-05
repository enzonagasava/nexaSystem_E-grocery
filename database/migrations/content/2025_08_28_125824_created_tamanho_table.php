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
<<<<<<< HEAD
        Schema::dropIfExists('tamanhos');
=======
        Schema::connection('content')->dropIfExists('tamanhos');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
