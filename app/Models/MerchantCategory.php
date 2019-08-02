<?php

namespace App\Models;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class MerchantCategory extends Model
{
    //
    use AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }

    protected $table = 'mzl_merchant_category';
    protected $guarded = [];
    public $timestamps = true;

}
