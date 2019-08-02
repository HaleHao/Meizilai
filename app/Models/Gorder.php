<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gorder extends Model
{
    //商品订单表
    protected $table = 'mzl_gorder';
    protected $guarded = [];
    public $timestamps = true;


    public function order_goods()
    {
        return $this->hasMany(OrderGoods::class,'order_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

    //添加订单，并返回ID;
    public function addOrder(array $array=[])
    {
        return $this->create($array);
    }

    public function getDetail(array $where=[])
    {
        return $this->where($where)->first();
    }

    public function getGoodsList(array $where = [])
    {
        return $this->where($where)->with('order_goods')->orderBy('submit_time','desc')->get();
    }
    //更新订单
    public function updateOrder(array $where = [] ,array $arr = [])
    {
        return $this->where($where)->update($arr);
    }

    public function getGoodsDetail(array $where = [])
    {
        return $this->where($where)->with('order_goods')->first();
    }
}
