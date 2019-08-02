<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCart extends Model
{
    //用户购物车
    protected $table = 'mzl_shop_cart';
    protected $guarded = [];
    public $timestamps = true;


    public function goods()
    {
        return $this->belongsTo(Goods::class,'goods_id');
    }
    /**
     * 获取用户购物车
     * @param $where
     * @return mixed
     * Date: 2019/3/11 0011
     */
    public function getCart($where)
    {
        return $this->where($where)->with('goods')->orderBy('created_at','desc')->get();
    }

    /**
     * 获取购物车单个商品
     * @param $where
     * @return mixed
     * Date: 2019/3/11 0011
     */
    public function firstCart($where)
    {
        return $this->where($where)->first();
    }

    /**
     * 删除购物车
     * @param $where
     * @param $ids
     * @return mixed
     * Date: 2019/3/11 0011
     */
    public function delCart($where,$ids)
    {
        return $this->where($where)->destroy($ids);
    }

    /**
     * 添加购物车
     * @param $arr
     * @return mixed
     * Date: 2019/3/11 0011
     */
    public function addCart($arr)
    {
        return $this->insert($arr);
    }

}
