<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MaterialDetail extends Resource
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
            'title' => $this->title,
            'images' => GoodsImage::collection($this->images),
            'description' => $this->description,
            'created_at' => date($this->created_at),
            'is_like' => count($this->like)?1:0
        ];
    }
}
