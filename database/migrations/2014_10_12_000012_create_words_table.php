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
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            //$table->integer('ayat_no')->unsigned();
            $table->bigInteger('surah_no')->nullable();
            $table->bigInteger('ayat_no')->nullable();
            $table->foreignId('ayat_id')->constrained('infodata')->onDelete('cascade');
            $table->bigInteger('reference')->nullable();
            $table->string('word')->nullable();
            $table->string('root_word')->nullable();
            $table->string('seperate_root_word')->nullable();
            $table->foreignId('root_word_id')->constrained('root_word_meanings')->onDelete('cascade');
            $table->string('grammatical_description')->nullable();
            $table->string('grammer_detail')->nullable();
            $table->string('prefix')->nullable();
            $table->string('actual_word')->nullable();
            $table->string('filtered_word')->nullable();
            $table->string('postfix')->nullable();
            $table->string('addresser')->nullable();
            $table->string('addressee')->nullable();
            $table->string('word_refrences_types')->nullable();
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
        Schema::dropIfExists('words');
    }
};
