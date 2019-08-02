<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyIntroduction extends Model
{
    //公司介绍表
    protected $table = 'mzl_company_introduction';
    protected $guarded = [];
    public $timestamps = true;

    //获取公司信息
    public function getDetail()
    {
        return $this->first();
    }
}
