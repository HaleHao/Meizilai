<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StoreGorderList extends Resource
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
            'order_id' => $this->id,
            'username' => $this->username,
            'total_price' => $this->total_price,
            'order_sn' => $this->order_sn,
            'order_status' => $this->order_status,
            'submit_time' => date('Y-m-d H:i:s',$this->submit_time),
            'address' => $this->province.$this->city.$this->district.$this->address,
            'cancel_remark' => $this->cancel_remark,
            'delivery_method' => $this->delivery_method,
            'goods' => OrderGoodsList::collection($this->order_goods),
        ];
    }
}
