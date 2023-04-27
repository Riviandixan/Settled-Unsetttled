<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportcpopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportcpop', function (Blueprint $table) {
            $table->integer('MID');
            $table->integer('PARENT');
            $table->integer('ORIG_MID');
            $table->integer('TID');
            $table->integer('POS_BATCH');
            $table->integer('NBRTXN');
            $table->string('SETTLE_DATE');
            $table->string('SETTLE_TIME');
            $table->string('TRAN_DATE');
            $table->string('TRAN_TIME');
            $table->integer('NPG_BIN');
            $table->string('CARD_NUMBER');
            $table->string('CARD_TYPE');
            $table->integer('AUTH_CODE');
            $table->integer('MDR');
            $table->integer('TRAN_AMOUNT');
            $table->integer('DICS_AMOUNT');
            $table->integer('NETT_AMOUNT');
            $table->string('TRAN_DESCRIPTION');
            $table->string('BANK_NAME');
            $table->integer('BANK_ACCOUNT');
            $table->string('BANK_ACCOUNT_NAME');
            $table->integer('ENTRY_MODE');
            $table->integer('PAGE_FLATFEE');
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
        Schema::dropIfExists('reportcpop');
    }
}
