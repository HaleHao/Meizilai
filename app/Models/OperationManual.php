<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationManual extends Model
{
    //操作引导
    protected $table = 'mzl_operation_manual';
    protected $guarded = [];
    public $timestamps = true;

    public function getDetail()
    {
        return $this->first();
    }
}
