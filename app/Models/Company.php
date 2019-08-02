<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //公司信息表
    protected $table = 'mzl_company';
    protected $guarded = [];
    public $timestamps = true;
}
