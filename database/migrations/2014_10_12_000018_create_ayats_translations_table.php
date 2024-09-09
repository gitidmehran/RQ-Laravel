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
        Schema::create('ayats_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayat_id')->nullable()->constrained('infodata')->onDelete('cascade');
            $table->foreignId('scholar_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('language_id')->nullable()->constrained('languages')->onDelete('cascade');
            $table->string('translation', 10000)->nullable();//->change();
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
        Schema::dropIfExists('ayats_translations');
    }
};
