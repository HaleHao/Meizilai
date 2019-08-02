<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    //商家
    protected $table = 'mzl_merchant';
    protected $guarded = [];
    public $timestamps = true;

    public  function category()
    {
        return $this->belongsTo(MerchantCategory::class,'category_id');
    }

}
