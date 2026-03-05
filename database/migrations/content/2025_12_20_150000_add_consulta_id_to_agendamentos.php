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
        Schema::table('agendamentos', function (Blueprint $table) {
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
    }
};
