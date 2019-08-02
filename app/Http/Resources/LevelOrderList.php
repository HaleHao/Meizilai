<?php

namespace App\Http\Resources;

use App\Models\MemberCard;
use App\Models\Users;
use Illuminate\Http\Resources\Json\Resource;

class LevelOrderList extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = Users::where('id',$this->user_id)->with('card')->first();
        return [
            'order_id' => $this->id,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'order_status' => $this->order_status,
            'reg_time' => date('Y-m-d',$user->reg_time),
            'submit_time' => date('Y-m-d',$this->submit_time),
            'card_name' => data_get($user->card,'name'),
            'level_name' => $this->level_name
        ];
    }
}
