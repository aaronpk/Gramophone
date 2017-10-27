<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePodcasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id');
            $table->string('url', 512);
            $table->string('micropub_endpoint', 512);
            $table->string('media_endpoint', 512);
            $table->text('access_token');

            $table->string('name', 512)->nullable();
            $table->string('author', 512)->nullable();
            $table->string('genre', 512)->nullable();
            $table->string('cover_image', 512)->nullable();

            $table->integer('last_episode_number')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('podcasts');
    }
}
