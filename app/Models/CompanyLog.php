<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLog extends Model
{
    //公司收益日志
    protected $table = 'mzl_company_earnings_log';
    protected $guarded = [];
    public $timestamps = true;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
