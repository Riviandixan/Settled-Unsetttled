<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MERCHTXNR extends Model
{
    use HasFactory;

    protected $table = 't_settledds';

    protected $guarded = [];

    public $timestamps = false;

    public function Txn_desc()
    {
        return $this->hasOne(MERCHTXNR::class, 'TID', 'TID');
    }

    public function CPOP()
    {
        return $this->hasMany(CPOP::class, 'TID', 'TID');
    }

    // protected $fillable = [
    //     'MID',
    //     'PARENT',
    //     'ORIG_MID',
    //     'TID',
    //     'POS_BATCH',
    //     'NBR_TXN',
    //     'SETTLE_DTE',
    //     'SETTLE_TME',
    //     'TXN_DTE',
    //     'TXN_TIME',
    //     'CARD_NBR',
    //     'CARD_TYPE',
    //     'AUTH_CODE',
    //     'MDR',
    //     'TXN_AMT',
    //     'DISC_AMT',
    //     'NET_AMT',
    //     'TXN_DESC',
    //     'BANK_NAME',
    //     'ACCT_NBR',
    //     'ACCT_NAME',
    //     'ENTRY_MODE',
    //     'FLAT_FEE'
    // ];
}
