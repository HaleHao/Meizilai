<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GraphicDetail extends Resource
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
            'cover_url' => url('uploads/'.$this->cover_url),
            'description' => $this->description,
            'created_at' => date($this->created_at),
        ];
    }
}
