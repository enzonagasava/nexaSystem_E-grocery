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
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('tipo')->default('ecommerce')->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'tipo')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
    }
};
