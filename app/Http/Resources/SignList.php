<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SignList extends Resource
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
            'sign_time' => date('Y/m/d',$this->sign_time)
        ];
    }
}
