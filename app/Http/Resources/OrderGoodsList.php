<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class OrderGoodsList extends Resource
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
            'goods_id' => $this->goods_id,
            'order_id' => $this->order_id,
            'goods_num' => $this->goods_num,
            'goods_price' => $this->goods_price,
            'goods_name' => $this->goods_name,
            'goods_img' => url('uploads/'.$this->goods_img),
            'total_price' => $this->total_price,
            'submit_time' => date('Y-m-d H:i:s',$this->submit_time),
        ];
    }
}
