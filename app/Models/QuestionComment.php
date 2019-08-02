<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionComment extends Model
{
    //百问百答评论表
    protected $table = 'mzl_question_comment';
    protected $guarded = [];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id')->select('id','avatar','nickname');
    }


    public function getList($where)
    {
        return $this->with('user')->where($where)->get();
    }

    public function addComment($arr)
    {
        return $this->create($arr);
    }
}
