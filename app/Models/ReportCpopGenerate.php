<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportCpopGenerate extends Model
{
    use HasFactory;

    protected $table = 't_settleds_staging';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'MID',
        'TID',
        'ACCT_NAME',
        'Status',
        'Path',
        'TXN_DTE'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->incrementing = false;
            $model->keyType = 'string';
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }

    public function Txn_desc()
    {
        return $this->hasOne(MERCHTXNR::class, 'TID', 'TID');
    }

    public function CPOP()
    {
        return $this->hasOne(CPOP::class, 'TID', 'TID');
    }
}
