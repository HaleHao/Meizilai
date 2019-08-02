<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //用户地址表
    protected $table = 'mzl_user_address';
    protected $guarded = [];
    public $timestamps = true;

    //获取默认地址
    public function getDefault(array $where=[])
    {
        return $this->where($where)->orderBy('is_default','desc')->first();
    }
}
