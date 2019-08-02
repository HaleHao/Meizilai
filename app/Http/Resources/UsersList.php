<?php

namespace App\Http\Resources;

use App\Models\Users;
use Illuminate\Http\Resources\Json\Resource;

class UsersList extends Resource
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
            'avatar' => $this->avatar,
            'nickname' => $this->nickname,
            'reg_time' => date('Y-m-d',$this->reg_time),
            'count' => Users::where('first_user_id',$this->id)->count(),
        ];
    }
}
