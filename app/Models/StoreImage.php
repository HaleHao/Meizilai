<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreImage extends Model
{
    //店铺图片表
    protected $table = 'mzl_store_image';
    protected $guarded = [];
    public $timestamps = true;

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }
}
