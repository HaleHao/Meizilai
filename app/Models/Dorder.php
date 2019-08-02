<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dorder extends Model
{
    //设备订单编号
    protected $table = 'mzl_dorder';
    protected $guarded = [];
    public $timestamps = true;

}
