<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class GoodsCommentList extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $images = unserialize($this->images);
        $arr = [];
        foreach ($images as $image){
            $arr[] = url($image);
        }
        return [
            'id' => $this->id,
            'content' => $this->content,
            'star' => $this->star,
            'comment_time' => date('Y-m-d H:i',$this->comment_time),
            'images' => $arr,
            'user' => $this->user
        ];
    }
}
