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
        Schema::table('empresas', function (Blueprint $table) {
=======
        Schema::connection('content')->table('empresas', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->string('tipo')->default('ecommerce')->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<< HEAD
        if (Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'tipo')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
        }
=======
        Schema::connection('content')->table('empresas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
