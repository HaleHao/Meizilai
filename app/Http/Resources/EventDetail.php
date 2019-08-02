<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class EventDetail extends Resource
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
            'content' => $this->content,
            'is_show' => $this->is_show,
            'time' => date('Y-m-d H:i:s',strtotime($this->created_at))
        ];
    }
}
