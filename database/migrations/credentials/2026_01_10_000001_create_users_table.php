<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'credentials';

    public function up(): void
    {
        Schema::connection('credentials')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->unsignedBigInteger('empresa_id')->nullable(); // Adicione esta linha
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('credentials')->dropIfExists('users');
    }
};
