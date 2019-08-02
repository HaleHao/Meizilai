<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    //商品表
    protected $table = 'mzl_goods';
    protected $guarded = [];
    public $timestamps = true;

    public function images()
    {
        return $this->hasMany(GoodsImage::class,'goods_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }


    //获取热销列表
    public function getHotList()
    {
        return $this->where('is_hot',1)->where('is_put',1)->get();
    }

    //商品列表
    public function getList($where = [])
    {
        return $this->where($where)->orderBy('sort','desc')->get();
    }

    //商品详情
    public function getDetail($where)
    {
        return $this->where($where)->with('images')->first();
    }

    public function getCartList($where)
    {
        return $this->whereIn($where)->get();
    }

}
