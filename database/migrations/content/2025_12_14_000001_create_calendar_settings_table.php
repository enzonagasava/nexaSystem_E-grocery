<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
<<<<<<< HEAD
        Schema::create('calendar_settings', function (Blueprint $table) {
=======
        Schema::connection('content')->create('calendar_settings', function (Blueprint $table) {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            $table->id();
            $table->string('calendar_id')->nullable();
            $table->string('timezone')->nullable();
            $table->string('locale')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
<<<<<<< HEAD
        Schema::dropIfExists('calendar_settings');
=======
        Schema::connection('content')->dropIfExists('calendar_settings');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
};
