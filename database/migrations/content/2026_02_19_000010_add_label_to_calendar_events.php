<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('label')->nullable()->after('description');
            $table->string('label_color', 32)->nullable()->after('label');
        });
    }

    public function down()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['label', 'label_color']);
        });
    }
};
