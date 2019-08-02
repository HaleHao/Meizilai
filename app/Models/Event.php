<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //活动表
    protected $table = 'mzl_event';
    protected $guarded = [];
    public $timestamps = true;

}
