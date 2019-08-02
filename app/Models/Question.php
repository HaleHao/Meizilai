<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //百问百搭表
    protected $table = 'mzl_question';
    protected $guarded = [];
    public $timestamps = true;

    public function like()
    {
        return $this->hasMany(QuestionLike::class,'question_id');
    }

    public function comments()
    {
        return $this->hasMany(QuestionComment::class,'question_id');
    }
    /**
     * 获取百问百答列表
     * @param array $where
     * @return mixed
     * Date: 2019/3/8 0008
     */
    public function getList($where = [],$where2 = [])
    {
        return $this->where($where)->where('is_show',1)->with(['like'=>function($query) use($where2){
            $query->where($where2);
        }])->orderBy('sort','asc')->get();
    }

    /**
     * 百问百答详情
     * @param array $where
     * @return mixed
     * Date: 2019/3/8 0008
     */
    public function getDetail($where = [],$where2 = [])
    {
        return $this->where($where)->with(['like'=>function($query) use($where2){
            $query->where($where2);
        }])->first();
    }




}
