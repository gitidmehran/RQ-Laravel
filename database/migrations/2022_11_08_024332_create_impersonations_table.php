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
        Schema::create('impersonations', function (Blueprint $table) {
            $table->id();

            $table->string('personal_access_token_id', 100);//->constrained('oauth_access_tokens')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('impersonations', function (Blueprint $table) {
            $table->foreign('personal_access_token_id')->references('id')->on('oauth_access_tokens')->onDelete('cascade');/*
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('impersonations');
    }
};
