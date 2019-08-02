<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderServe extends Model
{
    //订单服务表
    protected $table = 'mzl_order_serve';
    protected $guarded = [];
    public $timestamps = true;

    public function serve()
    {
        return $this->belongsTo(StoreServe::class,'serve_id');
    }
}
