<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    //设备日志
    protected $table = 'mzl_device_log';
    protected $guarded = [];
    public $timestamps = true;

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
