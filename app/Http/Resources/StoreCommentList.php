<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StoreCommentList extends Resource
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
        foreach ($images as $image) {
            $arr[] = url($image);
        }
        return [
            'id' => $this->id,
            'grade' => $this->grade,
            'content' => $this->content,
            'comment_time' => date('Y-m-d',$this->comment_time),
            'user' => $this->user,
            'beautician' => $this->beautician,
            'images' => $arr,
        ];
    }
}
