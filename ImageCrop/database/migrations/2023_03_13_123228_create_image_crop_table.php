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
        Schema::create('image_crop', function (Blueprint $table) {
            $table->id();
            $table->string('original_name')->nullable();
            $table->string('file_name')->nullable();
            $table->string('crop_name')->nullable();
            $table->string('thumb_name')->nullable();
            $table->string('file_type')->nullable();
            $table->boolean('isvideo')->default(0);
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
        Schema::dropIfExists('image_crop');
    }
};
