<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beat_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->references('id')->on('genres');
            $table->foreignId('beat_id')->references('id')->on('beats');
            $table->unique(['beat_id', 'genre_id']);
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
        Schema::dropIfExists('beat_genre');
    }
};
