<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //轮播图
    protected $table = 'mzl_banner';
    protected $guarded = [];
    public $timestamps = true;

    //获取轮播图列表
    public function getList()
    {
        return $this->where('is_show',1)->orderBy('sort','desc')->take(config('banner_num',3))->get();
    }
}
