<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CartList extends Resource
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
            'goods' => GoodsList::make($this->goods),
            'total_num' => $this->total_num,
            'total_price' => $this->total_price
        ];
    }
}
