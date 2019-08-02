<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayLog extends Model
{
    //支付日志表
    protected $table = 'mzl_pay_log';
    protected $guarded = [];
    public $timestamps = true;
}
