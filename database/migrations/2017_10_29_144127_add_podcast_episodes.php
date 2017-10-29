<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPodcastEpisodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('podcast_id');
            $table->string('episode_url', 512);
            $table->string('audio_url', 512);

            $table->string('name', 512)->nullable();
            $table->string('date', 512)->nullable();
            $table->integer('episode')->default(0)->nullable();
            $table->string('duration', 10)->nullable();

            $table->text('summary')->nullable();
            $table->text('content')->nullable();

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
        Schema::dropIfExists('episodes');
    }
}
