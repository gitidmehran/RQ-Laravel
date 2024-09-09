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
        Schema::table('infodata', function (Blueprint $table) {
            $table->string('dottedless_arabic')->after('arabic_simple');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('infodata', function (Blueprint $table) {
            $table->dropColumn('dottedless_arabic');
        });
    }
};
