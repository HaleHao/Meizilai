<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GoodsDetail extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $images = $this->images;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'cover_url' => url('uploads/'.$this->cover_url),
            'mall_price' => $this->mall_price,
            'market_price' => $this->market_price,
            'is_buy' => $this->is_buy,
            'comment_num' => $this->comment_num,
            'grade' => $this->grade,
            'star' => $this->star,
            'images'  => GoodsImage::collection($images)
        ];
    }
}
