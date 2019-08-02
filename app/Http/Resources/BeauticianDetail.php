<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class BeauticianDetail extends Resource
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
            'name' => $this->username,
            'serve_num' => $this->serve_num,
//            'serve_status' => $this->serve_status,
            'avatar' => $this->avatar,
            'grade' => $this->grade,
            'store' => $this->store,
            'comment' => BeauticianCommentList::collection($this->comment)
        ];
    }
}
