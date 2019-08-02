<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class GorderTab extends AbstractTool
{
    protected function script()
    {
        $url = Request::fullUrlWithQuery(['tab' => '_tab_']);

        return <<<EOT

$('input:radio.user-tab').change(function () {

    var url = "$url".replace('_tab_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());
        $options = [
            'all' => '全部',
            9 => '待付款',
            1 => '待发货',
            2 => '待收货',
            3 => '待取货',
            4 => '待评价',
            5 => '已完成',
            6 => '已取消',
            7 => '待接单',
            8 => '已拒绝',
        ];

        return view('admin.tools.tab', compact('options'));
    }
}