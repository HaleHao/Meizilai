<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class LorderTab extends AbstractTool
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
            4 => '待付款',
            1 => '待审核',
            2 => '审核通过',
            3 => '审核拒绝',
        ];

        return view('admin.tools.tab', compact('options'));
    }
}