<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beat_id')->references('id')->on('beats');
            $table->string('type'); // in ARTWORK, PREVIEW, DOWNLOAD, ORIGINAL
            $table->string('file_type');
            $table->unsignedInteger('file_size'); // in bytes
            $table->string('name');
            $table->string('url')->nullable();
            $table->boolean('public')->default(false);
            $table->softDeletes();
            //

            $table->index('type');
            $table->index('file_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploads');
    }
}
