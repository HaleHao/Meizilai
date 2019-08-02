<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //宣传报道
    protected $table = 'mzl_report';
    protected $guarded = [];
    public $timestamps = true;

    public function like()
    {
        return $this->hasMany(ReportLike::class);
    }


    public function getDetail($where,$where2)
    {
        return $this->where('is_show',1)->where($where)->with(['like' => function ($query) use ($where2) {
            $query->where($where2);
        }])->first();
    }

    //关联点赞查询
    public function getList($where)
    {
        return $this->where('is_show',1)->with(['like' => function ($query) use ($where) {
            $query->where($where);
        }])->orderBy('sort','asc')->get();
    }
}
