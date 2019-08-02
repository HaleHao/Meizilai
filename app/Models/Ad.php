<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    //广告表
    protected $table = 'mzl_ad';
    protected $guarded = [];
    public $timestamps = true;
}
