<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyWithdrawLog extends Model
{
    //公司提现记录
    protected $table = 'mzl_company_withdraw_log';
    protected $guarded = [];
    public $timestamps = true;

}
