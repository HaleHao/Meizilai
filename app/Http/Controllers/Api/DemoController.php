<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;


class DemoController extends Controller
{
    //定时任务
    public function test()
    {
        $template = new TemplateController(13);
        $res = $template->sendTemplate();
        dd($res);
    }

}
