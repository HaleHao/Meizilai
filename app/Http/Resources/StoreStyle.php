<?php

namespace App\Http\Resources;

use App\Models\StoreImage;
use Illuminate\Http\Resources\Json\Resource;

class StoreStyle extends Resource
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
            'images' => StoreImages::collection($this->images)
        ];
    }
}
