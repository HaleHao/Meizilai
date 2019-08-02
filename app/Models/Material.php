<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    //素材圈
    protected $table = 'mzl_material';
    protected $guarded = [];
    public $timestamps = true;

    public function images()
    {
        return $this->hasMany(MaterialImage::class,'material_id');
    }

    public function like()
    {
        return $this->hasMany(MaterialLike::class,'material_id');
    }

    public function getList($where)
    {
        return $this->where('is_show',1)->with('images')->with(['like' => function($query) use($where){
            $query->where($where);
        }])->orderBy('sort','asc')->get();
    }

    public function getDetail($where,$where2)
    {
        return $this->where($where)->with('images')->with(['like' => function($query) use ($where2){
            $query->where($where2);
        }])->first();
    }
}
