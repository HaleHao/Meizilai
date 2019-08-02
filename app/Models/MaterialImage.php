<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialImage extends Model
{
    //素材圈图片
    protected $table = 'mzl_material_image';
    protected $guarded = [];
    public $timestamps = true;

    public function material()
    {
        return $this->belongsTo(Material::class,'material_id');
    }


}
