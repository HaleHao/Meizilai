<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCard extends Model
{
    //会员卡订单表
    protected $table = 'mzl_order_card';
    protected $guarded = [];
    public $timestamps = true;

    public function addCard(array $array = [])
    {
        return $this->create($array);
    }
}
