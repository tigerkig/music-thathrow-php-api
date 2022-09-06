<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePriceLimit extends Migration
{
    public function up()
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
        });
    }

    public function down()
    {
        Schema::table('beats', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
        });
    }
}
