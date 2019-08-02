<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

//Encore\Admin\Form::forget('map');

//会员消费类型标签
\Encore\Admin\Grid\Column::extend('useSpan', function ($value) {
    if ($value == 0){
        return "<span class='label label-success'>".data_get(config('global.use_type'),$value)."</span>";
    }else{
        return "<span class='label label-info'>".data_get(config('global.use_type'),$value)."</span>";
    }
});

//会员类型
\Encore\Admin\Grid\Column::extend('userType', function ($value) {
    if ($value == 0){
        return "<span class='label label-danger'>".data_get(config('global.user_type'),$value)."</span>";
    }elseif ($value == 1){
        return "<span class='label label-warning'>".data_get(config('global.user_type'),$value)."</span>";
    }elseif($value == 2){
        return "<span class='label label-default'>".data_get(config('global.user_type'),$value)."</span>";
    }elseif($value == 3){
        return "<span class='label label-info'>".data_get(config('global.user_type'),$value)."</span>";
    }else{
        return "<span class='label label-success'>".data_get(config('global.user_type'),$value)."</span>";
    }
});

//支付状态
\Encore\Admin\Grid\Column::extend('payStatus', function ($value) {
    if ($value == 0){
        return "<span class='label label-info'>".data_get(config('global.goods.pay_status'),$value)."</span>";
    }elseif ($value == 1){
        return "<span class='label label-success'>".data_get(config('global.goods.pay_status'),$value)."</span>";
    }elseif($value == 2){
        return "<span class='label label-danger'>".data_get(config('global.goods.pay_status'),$value)."</span>";
    }
});

\Encore\Admin\Grid\Column::extend('openMap', \App\Admin\Extensions\Tools\OpenMap::class);