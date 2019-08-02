<?php

namespace App\Http\Controllers;

class DemoController extends Controller
{
    public function Login()
    {
        $appid = "wx8cfda04e7a651509";
        $redirect_uri = urlencode("http://120.77.252.66/getWxCode");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
        header("Location:".$url);
    }


//    public function
}