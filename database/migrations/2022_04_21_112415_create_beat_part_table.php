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
        Schema::create('beat_part', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->references('id')->on('parts');
            $table->foreignId('beat_id')->references('id')->on('beats');
            $table->unique(['beat_id', 'part_id']);
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
        Schema::dropIfExists('beat_part');
    }
};
