<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreInfo extends Model
{
    //店铺信息表
    protected $table = 'mzl_store_info';
    protected $guarded = [];
    public $timestamps = true;

    public function getInfo(array $where = [])
    {
        return $this->where($where)->first();
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }
}
