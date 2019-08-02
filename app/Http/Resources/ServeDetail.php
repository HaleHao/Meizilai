<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ServeDetail extends Resource
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
            'order_sn' => $this->order_sn,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'order_status' => $this->order_status,
            'serve_time' => date('Y-m-d H:i',$this->serve_time),
            'card_level' => data_get(data_get($this->user,'card'),'name'),
            'submit_time' => date('Y-m-d H:i:s',$this->submit_time),
            'order_serve' => $this->orderServe,
            'total_price' => $this->total_price,
            'remark' => $this->remark
        ];
    }
}
