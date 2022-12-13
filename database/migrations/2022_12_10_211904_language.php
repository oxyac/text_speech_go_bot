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
        Schema::create('languages', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->timestamps();
            $table->text('code');
            $table->text('name');
            $table->string('voice_code', 50)->nullable()->unique();
            $table->text('voice_name')->nullable();
            $table->tinyText('gender')->nullable();
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id('local_id');
            $table->timestamps();
            $table->bigInteger('id');
            $table->text('username');
            $table->bigInteger('user_id');
            $table->text('first_name');
            $table->text('type');
            $table->unsignedTinyInteger('language_id')->nullable()->default(1);

            $table->foreign('language_id')->references('id')->on('languages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
        Schema::dropIfExists('languages');
    }
};
