<?php

namespace App\Http\Resources;

use App\Models\Sign;
use Illuminate\Http\Resources\Json\Resource;

class BeauticianList extends Resource
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
            'nickname' => $this->nickname,
            'username' => $this->username,
            'serve_num' => $this->serve_num,
            'serve_status' => Sign::where('sign_date',date('Y-m-d'))->where('user_id',$this->id)->first()?1:0,
            'avatar' => $this->avatar,
            'grade' => $this->grade
        ];
    }
}
