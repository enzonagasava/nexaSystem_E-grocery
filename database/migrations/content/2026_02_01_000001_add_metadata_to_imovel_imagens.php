<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToImovelImagens extends Migration
{
    public function up()
    {
        Schema::table('imovel_imagens', function (Blueprint $table) {
            if (!Schema::hasColumn('imovel_imagens', 'original_name')) {
                $table->string('original_name')->nullable()->after('ordem');
            }
            if (!Schema::hasColumn('imovel_imagens', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            if (!Schema::hasColumn('imovel_imagens', 'size')) {
                $table->bigInteger('size')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('imovel_imagens', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('size');
            }
        });
    }

    public function down()
    {
        Schema::table('imovel_imagens', function (Blueprint $table) {
            if (Schema::hasColumn('imovel_imagens', 'uploaded_at')) {
                $table->dropColumn('uploaded_at');
            }
            if (Schema::hasColumn('imovel_imagens', 'size')) {
                $table->dropColumn('size');
            }
            if (Schema::hasColumn('imovel_imagens', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
            if (Schema::hasColumn('imovel_imagens', 'original_name')) {
                $table->dropColumn('original_name');
            }
        });
    }
}
