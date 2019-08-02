<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StoreList extends Resource
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
            'address' => $this->province.$this->city.$this->district.$this->address,
            'distance' => $this->distance,
            'lat' => $this->lat,
            'lng' => $this->lng
        ];
    }
}
