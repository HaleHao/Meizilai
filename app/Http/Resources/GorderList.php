<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GorderList extends Resource
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
            'order_status' => $this->order_status,
            'order_goods' => OrderGoodsList::collection($this->order_goods)
        ];
    }
}
