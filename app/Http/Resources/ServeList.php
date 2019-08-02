<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ServeList extends Resource
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
            'order_status' => $this->order_status,
            'remark' => $this->remark,
            'cancel_time' => date('Y-m-d H:i:s',$this->cancel_time),
            'submit_time' => date('Y-m-d H:i:s',$this->submit_time),
            'serve_time' => $this->weekday($this->serve_time) . ' ' .date('m月d日 H:i',$this->serve_time),
            'username' => $this->user->username,
            'card_name' => data_get(data_get($this->user,'card'),'name'),
            'total_price' => $this->total_price,
            'beautician' => $this->beautician->username,
            'serve' => $this->orderServe
        ];
    }

    public function weekday($time)
    {
        if(is_numeric($time))
        {
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
            return $weekday[date('w', $time)];
        }
        return false;
    }

}
