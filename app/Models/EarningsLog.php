<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarningsLog extends Model
{
    //
    protected $table = 'mzl_earnings_log';
    protected $guarded = [];
    public $timestamps = true;

    public function getList(array $where=[])
    {
        return $this->where($where)->get();
    }


}
