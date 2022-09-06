<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('status')->default(0); // AWAITING_PAYMENT, FAILED_PAYMENT, COMPLETED_PAYMENT
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamp('completed_at')->nullable(true);
            $table->string('paypal_id')->nullable(true)->index();
            $table->integer('total');
            //
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
