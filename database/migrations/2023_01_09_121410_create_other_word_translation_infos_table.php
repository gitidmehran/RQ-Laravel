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
        Schema::create('other_word_translation_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained('words')->onDelete('cascade');
            $table->foreignId('scholar_id')->constrained('users')->onDelete('cascade');
            $table->string('addresser')->nullable();
            $table->string('addressee')->nullable();
            $table->string('quranic_lexicon')->nullable();
            $table->string('quranic_lexicon_type')->nullable();
            $table->integer('quranic_lexicon_number')->nullable();
            $table->boolean('disable')->nullable();
            $table->foreignId('reference_word')->nullable()->constrained('words')->onDelete('cascade');
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
        Schema::dropIfExists('other_word_translation_infos');
    }
};
