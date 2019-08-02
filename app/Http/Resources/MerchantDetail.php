<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MerchantDetail extends Resource
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
            'cover_url' => url('uploads/'.$this->cover_url),
            'description' => $this->description
        ];
    }
}
