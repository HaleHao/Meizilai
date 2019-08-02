<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class SorderTab extends AbstractTool
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
            8 => '待付款',
            1 => '待确认',
            2 => '已同意',
            3 => '已拒绝',
            4 => '已服务',
            5 => '待评价',
            6 => '已完成',
            7 => '已取消',
        ];

        return view('admin.tools.tab', compact('options'));
    }
}