<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    //商店表
    protected $table = "mzl_store";
    protected $guarded = [];
    public $timestamps = true;

    //店铺图片
    public function images()
    {
        return $this->hasMany(StoreImage::class,'store_id');
    }

    //店铺服务
    public function serve()
    {
        return $this->hasMany(StoreServe::class,'store_id');
    }

    //美容师列表
    public function beautician()
    {
        return $this->hasMany(StoreBeautician::class,'store_id');
    }

    /**
     * 获取商店列表
     * @return mixed
     * Date: 2019/3/8 0008
     */
    public function getList($where)
    {
        return $this->where($where)->with('images')->get();
    }

    public function getDetail($where)
    {
        return $this->where($where)->first();
    }

}
