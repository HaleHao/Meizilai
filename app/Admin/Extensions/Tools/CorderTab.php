<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class CorderTab extends AbstractTool
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
            2 => '待支付',
            1 => '已购买'
        ];

        return view('admin.tools.tab', compact('options'));
    }
}