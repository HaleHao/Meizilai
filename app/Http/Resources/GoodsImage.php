<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GoodsImage extends Resource
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
            'img_url' => url('uploads/'.$this->img_url)
        ];
    }
}
