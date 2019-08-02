<?php

namespace App\Http\Controllers\Api;

use App\Models\StoreInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class WechatPayController extends Controller
{
    //统一下单接口
    protected $unifiedorderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    protected $refundUrl = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    protected $app_id;

    protected $mch_secret;

    protected $mch_id;

    protected $store_id;

    protected $SSLCERT_PATH;

    protected $SSLKEY_PATH;

    protected $transfers = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';


    public function __construct($store_id)
    {

        $infoModel = new StoreInfo();
        $where[] = ['store_id', $store_id];
        $info = $infoModel->getInfo($where);
        $this->store_id = $store_id;
        $this->app_id = data_get($info,'wx_appid');
        $this->app_secret = data_get($info,'wx_secret');
        $this->mch_secret = data_get($info,'mch_secret');
        $this->mch_id = data_get($info,'mch_id');
        $this->SSLCERT_PATH = data_get($info,'apiclient_cert');
        $this->SSLKEY_PATH = data_get($info,'apiclient_key');
    }

    //下单
    public function ordering($out_trade_no, $total_fee, $spbill_create_ip, $product_id, $openid, $notifyUrl, $body)
    {
        if (!$this->store_id ){
            return jsonError('没有获取到店铺ID');
        }
        if (!$this->app_id){
            return jsonError('没有获取APP_ID');
        }
        if (!$this->mch_secret){
            return jsonError('没有获取到Mch_Secret');
        }
        if (!$this->mch_id){
            return jsonError('没有获取到Mch_ID');
        }
        $paydata = [
            'appid' => $this->app_id,
            'mch_id' => $this->mch_id,
            'device_info' => '公众号',
            'nonce_str' => $this->nonce_str(),
            'sign_type' => 'MD5',
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'fee_type' => 'CNY',
            'total_fee' => $total_fee,
            'spbill_create_ip' => $spbill_create_ip,
            'notify_url' => $notifyUrl,
            'trade_type' => 'JSAPI',
            'product_id' => $product_id,
            'openid' => $openid
        ];
//        dd($paydata);
        //添加签名
        $paydata['sign'] = $this->getSign($paydata);
        $paydata = $this->arrayToXml($paydata);
        $resultData = $this->postXmlOrJson($this->unifiedorderUrl, $paydata);
        //接收下单结果 返回格式是xml的
//        Log::info($resultData);
        $resultData = $this->XmlToArr($resultData);
        Log::info($resultData);
        // 在resultData 中就有微信服务器返回的prepay_id
        if ($resultData['return_code'] == 'SUCCESS') {
            if ($resultData['result_code'] == 'SUCCESS') {
                $reData = [
                    'appId' => $this->app_id,
                    'timeStamp' => (string)time(),
                    'nonceStr' => $this->nonce_str(),
                    'signType' => 'MD5',
                    'package' => 'prepay_id=' . $resultData['prepay_id']
                ];
                $reData['paySign'] = $this->getSign($reData);
                return $reData;
            }
        }
        return false;
    }

    //退款
    public function refund($transaction_id, $refund_no, $total_fee,$refund_desc,$refundNotifyUrl='')
        {
            $refund = [
                'appid' => $this->app_id,
                'mch_id' => $this->mch_id,
                'nonce_str' => $this->nonce_str(),
                'sign_type' => 'MD5',
                'transaction_id' => $transaction_id,//微信订单号
                'refund_desc' => $refund_desc,
                'out_refund_no' => $refund_no,//商家订单号
                'total_fee' => $total_fee,
                'refund_fee' => $total_fee,
                'notify_url' => $refundNotifyUrl//回调地址
            ];
            //添加签名
            $refund['sign'] = $this->getSign($refund);
            $refund = $this->arrayToXml($refund);
            Log::info($refund);
            $resultData = $this->postXmlSSLCurl($this->refundUrl, $refund);
            $resultData = $this->XmlToArr($resultData);
            Log::info($resultData);
            if ($resultData){
                if ($resultData['return_code'] == 'SUCCESS') {
                    if ($resultData['result_code'] == 'SUCCESS') {
                        $postData = [
                            'status' => 1,
                            'transaction_id' => $resultData['transaction_id'],
                        ];
                        return $postData;
                    }
                }
            }
            Log::info(json_encode($resultData));
    //        return ['status' => 0, 'msg' => $resultData['return_msg']];
            return false;
        }

    //提现
    public function transfers($partner_trade_no, $openid, $amount, $desc)
    {
        $transfers = [
            'mch_appid' => $this->app_id,
            'mchid' => $this->mch_id,
            'nonce_str' => $this->nonce_str(),
            'sign_type' => 'MD5',
            'partner_trade_no' => $partner_trade_no,
            'openid' => $openid,
            'check_name' => 'NO_CHECK',
            'amount' => $amount,
            'desc' => $desc,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR']
        ];
        //添加签名
        $transfers['sign'] = $this->getSign($transfers);

        $transfers = $this->arrayToXml($transfers);
        Log::info($transfers);
        $resultData = $this->postXmlSSLCurl($this->transfers, $transfers);
        Log::info($resultData);
        $resultData = $this->XmlToArr($resultData);
        if ($resultData['return_code'] == 'SUCCESS') {
            if ($resultData['result_code'] == 'SUCCESS') {
                $postData = [
                    'status' => 1
                ];
                return $postData;
            }
        }
        Log::info(json_encode($resultData));
        return ['status' => 0, 'msg' => $resultData['return_msg']];
    }

    //扫码支付
    public function createJsBizPackage($totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp)
    {
        $config = array(
            'mch_id' => $this->mch_id,
            'appid' => $this->app_id,
            'key' => $this->mch_secret,
        );
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = array(
            'appid' => $config['appid'],
            'attach' => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'total_fee' => intval($totalFee * 100),       //单位 转为分
            'trade_type' => 'NATIVE',
        );
        $unified['sign'] = self::getNoSign($unified, $config['key']);
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        $codeUrl = (array)($unifiedOrder->code_url);
        if(!$codeUrl[0]) exit('get code_url error');
        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => $timestamp,
            "nonceStr" => self::createNonceStr(),
            "package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType" => 'MD5',
            "code_url" => $codeUrl[0],
        );
        $arr['paySign'] = self::getNoSign($arr, $config['key']);
        return $arr;
    }

    public static function getNoSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function notify_url($data)
    {

        if ($data == null) {
            Log::info('支付回调信息为空');
        }
        Log::info('支付回调信息:' . $data);
        $data = $this->XmlToArr($data);
        if ($data['return_code'] == 'SUCCESS') {
            //验签
            $sign = $data['sign'];
            unset($data['sign']);
            $mysign = $this->getSign($data);
            if ($mysign == $sign) {
                //签名验证成功
                if ($data['result_code'] == 'SUCCESS') {
                    return $data;
                } else {
                    $error = ['return_code' => 'FAIL', 'return_msg' => '支付失败'];
                    echo $this->arrayToXml($error);
                }
            } else {
                $error = ['return_code' => 'FAIL', 'return_msg' => '签名失败'];
                echo $this->arrayToXml($error);
            }
        }
    }



        //生成随机字符串
        protected function nonce_str()
        {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-';
            $random = $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)];
            $content = uniqid() . $random;
            return md5(sha1($content));
        }

    //生成签名
    protected function getSign($data)
    {
        //去除数组空键值
        $data = array_filter($data);
        //如果数组中有签名删除签名
        if (isset($data['sing'])) {
            unset($data['sing']);
        }
        //按照键名字典排序
        ksort($data);

        $str = http_build_query($data) . "&key=" . $this->mch_secret;

        //转码
        $str = $this->arrToUrl($str);

        return strtoupper(md5($str));
    }

    //URL解码为中文
    public function arrToUrl($str)
    {
        return urldecode($str);
    }

    //转换xml
    public function arrayToXml($arr)
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

    //Xml转数组
    public function XmlToArr($xml)
    {
        if ($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    //提交XML方法
    protected function postXmlOrJson($url, $data)
    {
        //$data = 'XML或者JSON等字符串';
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $data;

        //防止curl请求 https站点报错 禁用证书验证
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;


        //curl_setopt($ch, CURLOPT_SSLCERT,app_path('/Cert/apiclient_cert.pem'));
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        Log::info($content);
        return $content;
    }

    //需要使用证书的请求
    function postXmlSSLCurl($url, $xml, $second = 30)
    {

        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, public_path('uploads/'.$this->SSLCERT_PATH));
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, public_path('uploads/'.$this->SSLKEY_PATH));
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            Log::info('curl出错，错误码' . $error);
            curl_close($ch);
            return false;
        }
    }

    //退款通知处理
    public function refundNotify($data)
    {
        if ($data == null) {
            Log::info('退款回调信息为空');
        }
        Log::info('退款回调信息:' . $data);
        $data = $this->XmlToArr($data);
        if ($data['return_code'] == 'SUCCESS') {
            $req_info = $data['req_info'];
            $req_info = base64_decode($req_info);
            $keys = md5($this->key);
            $reqDataXml = Tool::AesDecode($keys, $req_info);
            Log::info('退款加密字符串解码报文:' . $reqDataXml);
            $reqData = $this->XmlToArr($reqDataXml);
            return $reqData;
        } else {
            return false;
        }
    }

    //封装提现方法
    protected function curl_post_ssl($url, $vars, $second = 30, $aHeader = array())
    {
        $isdir = "/cert/";//证书位置

        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }


}