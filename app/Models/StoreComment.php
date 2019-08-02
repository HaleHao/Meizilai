<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreComment extends Model
{
    //店铺评论表
    protected $table = 'mzl_store_comment';
    protected $guarded = [];
    public $timestamps = true;

    public function beautician()
    {
        return $this->belongsTo(Users::class,'beautician_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id')->select(['id','avatar','nickname']);
    }


    public function getList($where)
    {
        return $this->where($where)->with(['beautician' => function($query){
            $query->select(['id','username','nickname']);
        }])->with(['user' => function($query){
            $query->select(['id','avatar','nickname']);
        }])->get();
    }

    public function getNum(array $where = [])
    {
        return $this->where($where)->count('id');
    }


    public function addComment(array $arr =[])
    {
        return $this->create($arr);
    }

    public function getCommentList(array $where=[])
    {
        return $this->where($where)->get();
    }

}
