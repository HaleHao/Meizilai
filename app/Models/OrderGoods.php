<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    //商品订单表
    protected $table = 'mzl_order_goods';
    protected $guarded = [];
    public $timestamps = true;

    //获取订单商品列表
    public function getList()
    {

    }
}
