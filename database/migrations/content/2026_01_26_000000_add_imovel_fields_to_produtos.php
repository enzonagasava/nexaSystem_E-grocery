<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImovelFieldsToProdutos extends Migration
{
    public function up()
    {
        // No-op migration kept for history: imoveis are stored in their own table.
    }

    public function down()
    {
        // no-op
    }
}
