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
        Schema::create('infodata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')->nullable()->constrained('Quran_surah')->onDelete('cascade');
            $table->bigInteger('surahNo')->nullable();
            $table->bigInteger('ayatNo')->nullable();
            $table->string('arabic', 2500)->nullable();
            $table->string('arabic_simple', 2500)->nullable();
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
        Schema::dropIfExists('infodata');
    }
};
