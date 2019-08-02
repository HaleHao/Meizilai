<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sorder extends Model
{
    //服务订单表
    protected $table = 'mzl_sorder';
    protected $guarded = [];
    public $timestamps = true;

    public function orderServe()
    {
        return $this->hasMany(OrderServe::class,'order_id');
    }

    public function beautician()
    {
        return $this->belongsTo(Users::class,'beautician_id')->select(['id','avatar','username']);
    }

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id')->select(['id','name']);
    }


    public function getServeList(array $where = [])
    {
        return $this->where($where)->with(['user' => function($query){
            return $query->with('card');
        }])->with('orderServe')->with('beautician')->orderBy('submit_time','desc')->get();
    }


    public function getServeDetail(array $where = [])
    {
        return $this->where($where)->with(['orderServe','user' => function($query){
            return $query->with('card');
        }])->first();
    }


    public function getDetail(array $where = [])
    {
        return $this->where($where)->first();
    }


    public function addOrder(array $array=[])
    {
        return $this->create($array);
    }



    public function updateOrder(array $where = [] ,array $arr = [])
    {
        return $this->where($where)->update($arr);
    }



    public function getOrder(array $where = [])
    {
        return $this->where($where)->with('orderServe','beautician','store')->get();
    }
}
