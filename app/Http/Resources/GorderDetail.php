<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GorderDetail extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_sn' => $this->order_sn,
            'total_price' => $this->total_price,
            'goods_num' => $this->goods_num,
            'order_status' => $this->order_status,
            'pay_status' => $this->pay_status,
            'delivery_method' => $this->delivery_method,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'address' => $this->province.$this->city.$this->district.$this->address,
            'remark' => $this->remark,
            'submit_time' => $this->submit_time,
            'goods_price' => $this->goods_price,
            'carriage' => $this->carriage,
            'order_goods' => OrderGoodsList::collection($this->order_goods)
        ];
    }
}
