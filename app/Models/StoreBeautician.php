<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBeautician extends Model
{
    //店铺美容师表
    protected $table = "mzl_store_beautician";
    protected $guarded = [];
    public $timestamps = true;

    public function store()
    {
        return $this->belongsTo(Store::class,'store_id')->select(['id','name','province','city','district','address','lng','lat']);
    }

    public function comment()
    {
        return $this->hasMany(StoreComment::class,'beautician_id');
    }

    public function getList($where)
    {
        return $this->where($where)->orderBy('praise','desc')->get();
    }


    public function getDetail($where)
    {
        return $this->where($where)->with('store','comment.user')->first();
    }

}
