<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawLog extends Model
{
    //
    protected $table = 'mzl_withdraw_log';
    protected $guarded = [];
    public $timestamps = true;


    public function getList(array $where=[])
    {
        return $this->where($where)->get();
    }
}
