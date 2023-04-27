<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reportcpop';
    protected $guarded = [];

    protected $fillable = [
        // 'MID',
        // 'PARENT',
        // 'ORIG_MID',
        // 'TID',
        // 'POS_BATCH,
        // 'NBRTXN',
        // 'SETTLENT_DATE',
        // 'SETTLE_TIME',
        // 'TRAN_DATE',
        // 'TRAN_TIME',
        // 'NPG_BIN'
        // 'CARD_NUMBER',
        // 'CARD_TYPE'
        // 'AUTH_CODE'
        // 'MDR',
        // 'TRAN_AMOUNT',
        // 'DISC_AMOUNT',
        // 'NETT_AMOUNT',
        // 'TRAN_DESCRIPTION',
        // 'BANK_NAME',
        // 'BANK_ACCOUNT',
        // 'BANK_ACCOUNT_NAME',
        // 'ENTRY_MODE',
        // 'PAGE_FLATFEE'
    ];
}
