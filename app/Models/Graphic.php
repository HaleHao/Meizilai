<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Graphic extends Model
{
    //图文专栏表
    protected $table = 'mzl_graphic';
    protected $guarded = [];
    public $timestamps = true;


    public function getList()
    {
        return $this->where('is_show',1)->orderBy('sort','asc')->get();
    }

    public function getDetail($where = [])
    {
        return $this->where('is_show',1)->where($where)->first();
    }

}
