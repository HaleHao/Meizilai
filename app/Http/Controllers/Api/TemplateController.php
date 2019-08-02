<?php

namespace App\Http\Controllers\Api;

use App\Models\StoreInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
    protected $app_id;

    protected $app_secret;

//    protected $mch_id;

    protected $store_id;

    public function __construct($store_id)
    {
        $infoModel = new StoreInfo();
        $where[] = ['store_id', $store_id];
        $info = $infoModel->getInfo($where);
        $this->store_id = $store_id;
        $this->app_id = data_get($info,'wx_appid');
        $this->app_secret = data_get($info,'wx_secret');

    }

    //发送模板消息
    public function sendTemplate($openid,$url,$data)
    {
        $result = $this->getAccessToken();
        $ACCESS_TOKEN = data_get($result,'accessToken');
//        $openid = ;//用户openid
        $template_id = 'HsOlSGXF-qiAKn7iUcLRdNiF565ZC0i-KVwayIHgkrw';//配置的模板id
//        $url = 'https://admin.gzchenyu.cn/admin/users';//点击模板消息跳转的链接

        $template = [
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'data' =>[
                'first' => array('value' => $data['first'], 'color' => "#173177"),
                'keyword1' => array('value' => $data['nickname'], 'color' => '#173177'),
                'keyword2' => array('value' => $data['mobile'], 'color' => '#173177'),
                'keyword3' => array('value' => $data['serve_name'], 'color' => '#173177'),
                'keyword4' => array('value' => $data['serve_time'], 'color' => '#173177'),
                'keyword5' => array('value' => $data['address'], 'color' => '#173177'),
                'remark' => array('value' => $data['remark'], 'color' => '#173177'),
            ]
        ];
        $send_template_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $ACCESS_TOKEN;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $send_template_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($template));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }


    //获取access_token
    public function getAccessToken()
    {
        // access_token 应该全局存储与更新
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->app_id . "&secret=" . $this->app_secret;
        // 微信返回的信息
        $returnData = json_decode($this->httpGet($url));

        if ($returnData) {
            // 组装数据
            $resData['accessToken'] = data_get($returnData,'access_token');
            $resData['expiresIn'] =  data_get($returnData,'expires_in');
            $resData['time'] = date("Y-m-d H:i", time());
            // 把数据存进数据库
            return $resData;
        }
        return false;
    }


    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }


}
