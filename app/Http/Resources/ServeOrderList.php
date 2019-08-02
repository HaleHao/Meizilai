<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ServeOrderList extends Resource
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
            'order_status' => $this->order_status,
            'pay_status' => $this->pay_status,
            'serve_time' => $this->weekday($this->serve_time) . ' ' .date('m月d日 H:i',$this->serve_time),
//            'remark'
            'beautician' => $this->beautician,
            'store' => $this->store,
            'total_price' => $this->total_price,
            'serve' => OrderServeList::collection($this->orderServe)
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
