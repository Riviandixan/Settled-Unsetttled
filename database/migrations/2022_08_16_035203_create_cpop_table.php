<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpop', function (Blueprint $table) {
            $table->string('D');
            $table->integer('No');
            $table->string('Type');
            $table->string('Txn_date');
            $table->integer('TID');
            $table->string('Merch_Num');
            $table->string('Merch_Name');
            $table->string('City');
            $table->string('Txn_code');
            $table->integer('Txn_sts_code');
            $table->string('Txn_stts');
            $table->integer('Txn_numb');
            $table->string('Txn_total');
            $table->integer('point');
            $table->string('filename');
            $table->date('settlementDate');
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
        Schema::dropIfExists('cpop');
    }
}
