<?php

namespace App\Exports;

use App\Models\CPOP;
use App\Models\MasterMidTid;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CpopExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $Txn_date;
    private $TID;
    private $Merch_Num;
    private $Type;
    private $headers;

    public function __construct($val)
    {
        // dd($val);
        $this->Txn_date = $val->Txn_date;
        $this->TID = $val->TID;
        $this->Merch_Num = $val->Merch_Num;
        $this->Type = $val->Type;
    }

    public function collection()
    {
        // dd($this->Txn_date,$this->TID,$this->Merch_Num,$this->Type);
        return CPOP::
        select(
            "D",
            "Txn_date",
            "TID",
            "Merch_Num",
            "Merch_Name",
            "City",
            "Txn_code",
            "Txn_sts_code",
            "Txn_stts",
            "Txn_Numb",
            "Txn_Total",
            "Point"
        )
        ->where('Txn_date', $this->Txn_date)
        ->Where('TID', $this->TID)
        ->Where('Merch_Num', $this->Merch_Num)
        ->Where('Type', $this->Type)->get();
    }

    public function headings(): array
    {
        return ["D",
        "Txn_date",
        "TID",
        "Merch_Num",
        "Merch_Name",
        "Alamat",
        "PIC Merchant",
        "No Telepon",
        "City",
        "Txn_code",
        "Txn_sts_code",
        "Txn_stts",
        "Txn_Numb",
        "Txn_Total",
        "Point"];
    }
}
