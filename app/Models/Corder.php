<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Corder extends Model
{
    //会员卡订单表
    protected $table = 'mzl_corder';
    protected $guarded = [];
    public $timestamps = true;

    //添加订单，并返回ID;
    public function addOrder(array $array=[])
    {
        return $this->create($array);
    }

    public function getDetail(array $where=[])
    {
        return $this->where($where)->first();
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }

    public function card()
    {
        return $this->belongsTo(MemberCard::class,'card_id');
    }
}
