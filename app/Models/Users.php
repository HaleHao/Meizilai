<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    //用户表
    protected $table = "mzl_users";
    protected $guarded = [];
    public $timestamps = true;

    public function address()
    {
        return $this->hasMany(UserAddress::class,'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id');
    }

    public function comment()
    {
        return $this->hasMany(StoreComment::class,'beautician_id');
    }

    public function card()
    {
        return $this->belongsTo(MemberCard::class,'card_id');
    }

    public function firstUser()
    {
        return $this->belongsTo(Users::class,'first_user_id');
    }

    public function level()
    {
        return $this->belongsTo(MemberLevel::class,'level_id');
    }

    public function getUserInfo(array $where=[])
    {
        return $this->where($where)->first();
    }

    public function getBeauticianList(array $where=[])
    {
        return $this->where($where)->select('id','avatar','username','nickname','grade','serve_num')->get();
    }

    //更新用户信息
    public function updateInfo(array $where = [],array $attributes = [])
    {
        return $this->where($where)->update($attributes);
    }

    //获取用户信息
    public function getInfo(array $where = [])
    {
        return $this->where($where)->first();
    }


    //获取用户默认地址
    public function getAddress(array $where=[])
    {
        return $this->where($where)->with('address')->first();
    }

    //获取下级用户
    public function getTeam(array $where=[])
    {
        return $this->where($where)->get();
    }
}
