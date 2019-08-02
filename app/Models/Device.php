<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //设备仪器表
    protected $table = 'mzl_device';
    protected $guarded = [];
    public $timestamps = true;

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }
}
