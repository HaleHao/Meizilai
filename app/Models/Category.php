<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Category extends Model
{
    use AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }
    //商品分类表
    protected $table = 'mzl_category';
    protected $guarded = [];
    public $timestamps = true;


    public function goods()
    {
        return $this->hasMany(Goods::class,'category_id');
    }

    //获取分类列表
    public function getList($where)
    {
        return $this->where($where)->orderBy('order','asc')->get();
    }

}
