<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CPOP extends Model
{
    use HasFactory;

    protected $table = 't_unsettled';

    protected $primaryKey = 'No';
    protected $guarded = [];

    public $timestamps = false;

    // protected $fillable = [
    //     'D',
    //     'No',
    //     'Type',
    //     'Txn_date',
    //     'TID',
    //     'Merch_Num',
    //     'Merch_Name',
    //     'City',
    //     'Txn_code',
    //     'Txn_sts_code',
    //     'Txn_stts',
    //     'Txn_numb',
    //     'Txn_total',
    //     'point'
    // ];

}
