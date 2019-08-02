<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsImage extends Model
{
    //商品图片表
    protected $table = 'mzl_goods_image';
    protected $guarded = [];
    public $timestamps = true;

    public function good()
    {
        return $this->belongsTo(Goods::class,'goods_id');
    }
}
