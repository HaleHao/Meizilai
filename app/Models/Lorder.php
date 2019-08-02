<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lorder extends Model
{
    //等级订单表
    protected $table = 'mzl_lorder';
    protected $guarded = [];
    public $timestamps = true;


    public function getLevelList(array $where = [])
    {
        return $this->where($where)->get();
    }

    public function level()
    {
        return $this->belongsTo(MemberLevel::class,'level_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

    public function first()
    {
        return $this->belongsTo(Users::class,'first_user_id');
    }
}
