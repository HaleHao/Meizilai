<?php

namespace App\Http\Resources;

use App\Models\QuestionComment;
use Illuminate\Http\Resources\Json\Resource;

class ReportDetail extends Resource
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
            'description' => $this->description,
            'cover_url' => url('uploads/'.$this->cover_url),
            'created_at' => date($this->created_at),
            'is_like' => count($this->like)?1:0,
        ];
    }
}
