<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sign extends Model
{
    //签到表
    protected $table = 'mzl_sign';
    protected $guarded = [];
    public $timestamps = true;


    public function getSignList(array $where =[],$first_day = '',$last_day = '')
    {
        return $this->where($where)->whereBetween('sign_time',[$first_day,$last_day])->get();
    }

    public function getSignDetail(array $where =[])
    {
        return $this->where($where)->first();
    }

    public function addSign(array $arr=[])
    {
        return $this->create($arr);
    }
}
