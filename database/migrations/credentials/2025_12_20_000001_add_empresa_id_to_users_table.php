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
        Schema::connection('credentials')->table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->after('cargo_id');
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('credentials')->table('users', function (Blueprint $table) {
            $table->dropIndex(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};
