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
<<<<<<< HEAD
        Schema::table('agendamentos', function (Blueprint $table) {
=======
        Schema::connection('content')->table('agendamentos', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->foreignId('consulta_id')
                ->nullable()
                ->after('paciente_id')
                ->constrained('consultas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        if (Schema::hasTable('agendamentos') && Schema::hasColumn('agendamentos', 'consulta_id')) {
            try {
                Schema::table('agendamentos', function (Blueprint $table) {
                    $table->dropForeign(['consulta_id']);
                    $table->dropColumn('consulta_id');
                });
            } catch (\Exception $e) {
                // ignore if FK/column already removed
            }
        }
=======
        Schema::connection('content')->table('agendamentos', function (Blueprint $table) {
            $table->dropForeign(['consulta_id']);
            $table->dropColumn('consulta_id');
        });
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
