<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLevel extends Model
{
    //会员等级表
    protected $table = 'mzl_member_level';
    protected $guarded = [];
    public $timestamps = true;

    public function card()
    {
        return $this->belongsTo(MemberCard::class,'card_id');
    }


    //会员卡select选择框数据
    public function levelOptions()
    {
        return $this->pluck('name','id');
    }

}
