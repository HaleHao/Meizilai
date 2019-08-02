<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CommonController extends Controller
{

    /**
     * 获取用户ID
     */
    protected function getUserId()
    {
        $userInfo = session('wechat.oauth_user.default');
        $openId = data_get($userInfo,'id');
        $user_id = 0;
        if ($openId){
            $where = [
                'openid' => $openId
            ];
            $userModel = new Users();
            $user = $userModel->getUserInfo($where);
            $user_id = data_get($user,'id');
        }
        return $user_id;
    }

    /**
     * 无限级树状
     */
    protected function getTree($items,$pid ="parent_id") {
        $map  = [];
        $tree = [];
        foreach ($items as &$it){
            $map[$it['id']] = &$it;
        }  //数据的ID名生成新的引用索引树
        foreach ($items as &$at){
            $parent = &$map[$at[$pid]];
            if($parent) {
                $parent['children'][] = &$at;
            }else{
                $tree[] = &$at;
            }
        }
        return $tree;
    }

    /**
     * 距离计算
     */
    protected function getDistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    /**
     * 生成订单编号
     */
    protected function getOrderSn()
    {
        $sn = date('YmdHis').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) ;
        return $sn;
    }

    /**
     * 生成支付IP地址
     */
    protected function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }


    //生成token
    protected function getToken()
    {
        $str = md5(uniqid(md5(microtime(true)), true));  //生成一个不会重复的字符串
        $str = sha1($str);
        return $str;
    }

    /*
     * 用户信息保存到缓存
     */
    protected function userInfoSaveCache($user_id)
    {
        if (Cache::has($user_id)){
            $token = Cache::get($user_id);
        }else{
            $token = $this->getToken();
            Cache::forever($user_id,$token);
        }
        return $token;

    }

    /**
     * 生成二维码
     */
    protected function generateQrcode($url)
    {
        if(!file_exists(public_path('uploads/qrcodes'))){
            mkdir(public_path('uploads/qrcodes'),0777,true);
        }
        $name = date('YmdHis_') . str_random(8);
        $qrcode_name = 'uploads/qrcodes/' . $name . '.png';
        $url_name =  'qrcodes/' . $name . '.png';
        QrCode::format('png')->encoding('UTF-8')->margin(1)->size(500)->generate($url,public_path($qrcode_name));
        return $url_name;
    }

    /**
     * 转换array
     */
    protected function XmlToArr($xml)
    {
        if ($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    /**
     * 转换xml
     */
    protected function arrayToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml = $xml . '<' . $key . '>' . $this->arrayToXml($val) . '</' . $key . '>';
            } else {
                $xml = $xml . '<' . $key . '>' . $val . '</' . $key . '>';
            }

        }
        $xml .= '</xml>';
        return $xml;
    }
}