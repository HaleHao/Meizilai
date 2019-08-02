<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MerchantList extends Resource
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
            'id' => data_get($this,'id'),
            'name' => data_get($this,'name'),
            'cover_url' => url('uploads/'.data_get($this,'cover_url')),
            'address' => data_get($this,'province').data_get($this,'city').data_get($this,'district').data_get($this,'address'),
            'grade' =>data_get($this,'grade'),
            'lat' => data_get($this,'lat'),
            'lng' => data_get($this,'lng'),
            'distance' => data_get($this,'distance')
        ];
    }
}
