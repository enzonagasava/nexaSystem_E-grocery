<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToImovelVideos extends Migration
{
    public function up()
    {
        Schema::table('imovel_videos', function (Blueprint $table) {
            if (!Schema::hasColumn('imovel_videos', 'original_name')) {
                $table->string('original_name')->nullable()->after('path');
            }
            if (!Schema::hasColumn('imovel_videos', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            if (!Schema::hasColumn('imovel_videos', 'size')) {
                $table->bigInteger('size')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('imovel_videos', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('size');
            }
        });
    }

    public function down()
    {
        Schema::table('imovel_videos', function (Blueprint $table) {
            if (Schema::hasColumn('imovel_videos', 'uploaded_at')) {
                $table->dropColumn('uploaded_at');
            }
            if (Schema::hasColumn('imovel_videos', 'size')) {
                $table->dropColumn('size');
            }
            if (Schema::hasColumn('imovel_videos', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
            if (Schema::hasColumn('imovel_videos', 'original_name')) {
                $table->dropColumn('original_name');
            }
        });
    }
}
