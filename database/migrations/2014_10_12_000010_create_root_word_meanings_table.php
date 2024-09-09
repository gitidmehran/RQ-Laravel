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
        Schema::create('root_word_meanings', function (Blueprint $table) {
            $table->id();
            $table->string('english_root_word', 500)->nullable();
            $table->string('root_word', 500)->nullable();
            $table->string('seprate_root_word', 500)->nullable();
            $table->string('third', 500)->nullable();

            $table->string('second', 500)->nullable();
            $table->string('first', 500)->nullable();
            $table->string('meaning_urdu', 500)->nullable();
            $table->string('meaning_eng', 500)->nullable();
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
        Schema::dropIfExists('root_word_meanings');
    }
};
