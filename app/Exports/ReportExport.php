<?php

namespace App\Exports;

use App\Models\MERCHTXNR;
use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Psy\TabCompletion\Matcher\FunctionsMatcher;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    private $TID;
    private $MID;
    private $headers;

    public function __construct($val)
    {
        // dd($val);
        $this->MID = $val->MID;
        $this->TID = $val->TID;
    }

    public function collection()
    {
        // dd($this->MID, $this->TID);
        return MERCHTXNR::
        select(
            'MID',
            'PARENT',
            'ORIG_MID',
            'TID',
            'POS_BATCH',
            'NBR_TXN',
            'SETTLE_DTE',
            'SETTLE_TME',
            'TXN_DTE',
            'TXN_TIME',
            'CARD_NBR',
            'CARD_TYPE',
            'AUTH_CODE',
            'MDR',
            'TXN_AMT',
            'DISC_AMT',
            'NET_AMT',
            'TXN_DESC',
            'BANK_NAME',
            'ACCT_NBR',
            'ACCT_NAME',
            'ENTRY_MODE',
            'FLAT_FEE'
        )
        ->where('MID', $this->MID)
        ->where('TID', $this->TID)->get();
    }

    public function headings(): array
    {
        return ['MID',
        'PARENT',
        'ORIG_MID',
        'TID',
        'POS_BATCH',
        'NBR_TXN',
        'SETTLE_DTE',
        'SETTLE_TME',
        'TXN_DTE',
        'TXN_TIME',
        'CARD_NBR',
        'CARD_TYPE',
        'AUTH_CODE',
        'MDR',
        'TXN_AMT',
        'DISC_AMT',
        'NET_AMT',
        'TXN_DESC',
        'BANK_NAME',
        'ACCT_NBR',
        'ACCT_NAME',
        'ENTRY_MODE',
        'FLAT_FEE'];
    }
}
