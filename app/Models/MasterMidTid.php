<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterMidTid extends Model
{
    use HasFactory;

    protected $table = 't_unsettled_staging';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'Txn_date',
        'TID',
        'Merch_Num',
        'Type',
        'Status',
        'Path'
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

    public function CPOP()
    {
        return $this->hasOne(CPOP::class, 'TID', 'TID');
    }
}
