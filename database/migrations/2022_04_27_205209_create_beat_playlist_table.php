<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeatPlaylistTable extends Migration
{
    public function up()
    {
        Schema::create('beat_playlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beat_id')->references('id')->on('beats');
            $table->foreignId('playlist_id')->references('id')->on('playlists');
            $table->unique(['beat_id', 'playlist_id']);
            //

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('beat_playlist');
    }
}
