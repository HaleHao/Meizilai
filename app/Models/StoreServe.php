<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreServe extends Model
{
    //店铺服务表
    protected $table = 'mzl_store_serve';
    protected $guarded = [];
    public $timestamps = true;


    /**
     * 获取服务列表
     * @param $where
     * @return mixed
     * Date: 2019/3/12 0012
     */
    public function getList($where)
    {
        return $this->where($where)->orderBy('created_at','desc')->get();
    }

    public function getDetail($where)
    {
        return $this->where($where)->first();
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }
}
