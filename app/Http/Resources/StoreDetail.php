<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StoreDetail extends Resource
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
//            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->province.$this->city.$this->district.$this->address,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'serve_check' => config('serve_check','radio'),
            'cover_url' => url('uploads/'.$this->cover_url),
//            'created_at' => $
        ];
    }
}
