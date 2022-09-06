<?php

use App\Models\Beat;
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
        Schema::create('beats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            // $table->string('artwork_url')->nullable();
            // $table->string('preview_url')->nullable();
            // $table->string('download_url')->nullable();
            // $table->string('purchase_url')->nullable();
            // $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('bpm')->default(60);
            // $table->unsignedInteger('duration')->nullable();
            $table->boolean('is_free');
            $table->boolean('is_exclusive');
            $table->boolean('download_enabled')->default(true);
            $table->boolean('purchase_enabled')->default(true);
            $table->foreignId('user_id')->references('id')->on('users');
            $table->smallInteger('status'); //['UNPRINTED', 'AVAILABLE', 'INACTIVE', 'PURCHASED' ]
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['is_exclusive']);
            $table->index(['is_free']);
            $table->index(['download_enabled']);
            $table->index(['purchase_enabled']);
            $table->index(['name']);
            $table->index(['price']);
            $table->index(['bpm']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beats');
    }
};
