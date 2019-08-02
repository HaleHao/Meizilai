<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class OrderServeList extends Resource
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
            'serve_name' => $this->serve_name
        ];
    }
}
