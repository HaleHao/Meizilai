<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMien extends Model
{
    //团队风采表
    protected $table = 'mzl_team_mien';
    protected $guarded = [];
    public $timestamps = true;


    public function getDetail()
    {
        return $this->first();
    }
}
