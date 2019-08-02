<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCard extends Model
{
    //会员卡表
    protected $table = "mzl_member_card";
    protected $guarded = [];
    public $timestamps = true;


    public function level()
    {
        return $this->belongsTo(MemberLevel::class,'level_id');
    }

    public function getList(array $where = [])
    {
        return $this->where($where)->orderBy('sort','desc')->get();
    }

    public function getDetail(array $where = [])
    {
        return $this->where($where)->first();
    }
}
