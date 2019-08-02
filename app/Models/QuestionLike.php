<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class QuestionLike extends Model
{
    //百问百答点赞
    protected $table = 'mzl_question_like';
    protected $guarded = [];
    public $timestamps = true;

    public function getDetail($where = [])
    {
        return $this->where($where)->first();
    }

    public function like($arr=[])
    {
        return $this->create($arr);
    }

    public function unlike($where = [])
    {
        return $this->where($where)->delete();
    }
}
