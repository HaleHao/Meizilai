<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialLike extends Model
{
    //素材圈点赞
    protected $table = 'mzl_material_like';
    protected $guarded = [];
    public $timestamps = true;

    public function getDetail($where = [])
    {
        return $this->where($where)->first();
    }

    public function like($arr=[])
    {
        return $this->create($arr);
    }

    public function unlike($where = [])
    {
        return $this->where($where)->delete();
    }
}
