<?php
<<<<<<< HEAD
=======
// database/migrations/xxxx_xx_xx_xxxxxx_create_status_table.php
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
<<<<<<< HEAD
{
        Schema::create('produtos', function (Blueprint $table) {
=======
<<<<<<<< HEAD:database/migrations/content/2026_02_05_172945_create_table_status.php
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            
            // Identificação do status
            $table->string('nome');
            $table->text('descricao')->nullable();
                    
            // Ordenação
            $table->integer('ordem')->default(0);
                        
            
            $table->softDeletes();
========
{
        Schema::connection('content')->create('produtos', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('estoque')->default(0);
<<<<<<< HEAD
=======
>>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f:database/migrations/content/2025_08_02_003813_create_produto_tabelas.php
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->timestamps();

            // Adiciona índice para performance (opcional mas recomendado)
            $table->index('user_id', 'produtos_user_id_index');

            // Índice composto se for comum buscar por usuário + data
            $table->index(['user_id', 'created_at'], 'user_produtos_chrono_index');

        });
    }
<<<<<<< HEAD
=======

>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        Schema::dropIfExists('produtos');
    }
};
=======
<<<<<<<< HEAD:database/migrations/content/2026_02_05_172945_create_table_status.php
        Schema::dropIfExists('status');
========
        Schema::connection('content')->dropIfExists('produtos');
>>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f:database/migrations/content/2025_08_02_003813_create_produto_tabelas.php
    }
};
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
