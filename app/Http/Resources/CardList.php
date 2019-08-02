<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CardList extends Resource
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
            'img_url' => url('uploads/'.$this->img_url),
            'mall_price' => $this->mall_price,
            'use_num' => $this->use_num,
            'level' => $this->level,
            'description' => $this->description
        ];
    }
}
