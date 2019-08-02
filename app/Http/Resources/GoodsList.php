<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GoodsList extends Resource
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
            'name' => $this->name,
            'title' => $this->title,
            'cover_url' => url('uploads/'.$this->cover_url),
            'mall_price' => $this->mall_price,
            'market_price' => $this->market_price,
        ];
    }
}
