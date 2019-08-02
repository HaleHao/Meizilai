<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsComment extends Model
{
    //商品评论表
    protected $table = 'mzl_goods_comment';
    protected $guarded = [];
    public $timestamps = true;

    public function goods()
    {
        return $this->belongsTo(Goods::class,'goods_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id')->select(['id','avatar','nickname']);
    }

    /**
     * 获取列表
     * @param array $where
     * @return mixed
     * Date: 2019/3/11 0011
     */
    public function getList($where = [])
    {
        return $this->where($where)->with('user')->orderBy('created_at','desc')->get();
    }

    /**
     * 获取星级
     * @param array $where
     * Date: 2019/3/11 0011
     */
    public function getStar($where = [])
    {
        return $this->where($where)->sum('star');
    }

    /**
     * 获取评论数量
     */
    public function getNum($where = [])
    {
        return $this->where($where)->count('id');
    }

    public function addComment(array $arr =[])
    {
        return $this->create($arr);
    }

}
