<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeatPurchaseTable extends Migration
{
    public function up()
    {
        Schema::create('beat_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->references('id')->on('purchases');
            $table->foreignId('beat_id')->references('id')->on('beats');
            $table->integer('price');
            $table->unique(['beat_id', 'purchase_id']);
            $table->softDeletes();
            //

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('beat_purchase');
    }
}
