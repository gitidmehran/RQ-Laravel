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
        Schema::create('compound_words', function (Blueprint $table) {
            $table->id();
            // $table->integer('word_id')->unsigned();
            //$table->foreign('word_id')->references('id')->on('words')->onDelete('cascade');
            $table->foreignId('word_id')->nullable()->constrained('words')->onDelete('cascade');
            $table->string('word')->nullable();
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
        Schema::dropIfExists('compound_words');
    }
};
