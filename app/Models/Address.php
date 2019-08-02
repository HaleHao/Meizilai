<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //用户地址表
    protected $table = 'mzl_address';
    protected $guarded = [];
    public $timestamps = true;

}
