<?php

namespace App\Http\Controllers\Api;

use App\Models\StoreInfo;
use App\Models\Users;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class WeChatController extends CommonController
{

    protected $app_id;
    protected $app_secret;
    protected $redirect_url;
    protected $store_id;
    protected $url;

    public function __construct(Request $request)
    {
        $this->store_id = $request->input('store_id');
        $info = StoreInfo::where('store_id', $this->store_id)->first();
        Log::info('store-info:' . $info);
        Log::info('store_id:' . $request->input('store_id'));
        $this->app_id = data_get($info, 'wx_appid');
        $this->app_secret = data_get($info, 'wx_secret');
        $this->redirect_url = data_get($info, 'redirect_url');
        $this->url = $request->input('url', config('APP_URL', 'https://gzchenyu.cn'));
    }

    //登录入口
    public function wechatLogin(Request $request)
    {
        if (!$this->store_id) {
            return jsonError('没有获取到店铺ID');
        }
        if (!$this->app_id) {
            return jsonError('没有获取APP_ID');
        }
        if (!$this->app_secret) {
            return jsonError('没有获取到APP_Secret');
        }
        $url = $request->input('url');
        if ($url) {
            $redirect = urlencode($url);
        } else {
            $redirect = urlencode($this->redirect_url);
        }
        Log::info('++++++++++++++++++++++++++++' . $redirect);
        $redirect_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->app_id . '&redirect_uri=' . $redirect . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        return jsonSuccess($redirect_url);
//        exit;
    }

    //获取token
    public function wechatRegister(Request $request)
    {
        if (!$this->store_id) {
            return jsonError('没有获取到店铺ID');
        }
        if (!$this->app_id) {
            return jsonError('没有获取APP_ID');
        }
        if (!$this->app_secret) {
            return jsonError('没有获取到APP_Secret');
        }
        $code = $request->input('code');
//        $url = $request->input('url');
//        Log::info($code);
        if ($code) {
            //获取用户信息
            $data = $this->getWxInfo($code);
            if ($data) {
                try {

                    $openid = data_get($data, 'openid');

                    $userModel = new Users();
                    $where = [
                        'openid' => $openid
                    ];
                    $user = $userModel->getInfo($where);
                    $now = time();
                    //判断用户是否登陆
                    if ($user) {

                        $user->last_login = $now;

                        $user->last_ip = $_SERVER['REMOTE_ADDR'];
//
                        $user->nickname = data_get($data, 'nickname');

                        $user->avatar = data_get($data, 'headimgurl');

                        $user->subscribe = data_get($data,'subscribe');

                        $user->store_id = $this->store_id;

                        if (!$user->qrcode_path) {

                            $url = config('AD_URL', 'https://gzchenyu.cn/advertisement') . '?store_id=' . $this->store_id . '&first_user_id=' . $user->id;

                            $user->qrcode_path = $this->generateQrcode($url);

                        }

                        $user->save();

                        $token = $this->userInfoSaveCache($user->id);
                        $mobile_bing = 0;
                        if ($user->mobile) {
                            $mobile_bing = 1;
                        }
                        if ($token) {
                            $data = [
                                'token' => $token,
                                'is_mobile' => $mobile_bing,
                                'store_id' => $this->store_id
                            ];
                            return jsonSuccess($data, '登录成功');
                        }
                        return jsonError('token存储失败');
                    } else {

                        $user = new Users();

                        //保存上级ID
                        $first_user_id = 0;

                        $second_user_id = 0;

                        if ($request->input('first_user_id')) {

                            $pUser = Users::find($request->input('first_user_id'));

                            if ($pUser) {

                                $first_user_id = $pUser->id;

                                $second_user_id = $pUser->first_user_id;

                            }
                        } else {
                            $storekeeper = Users::where('store_id', $this->store_id)->where('user_type', 4)->where('is_storekeeper', 1)->first();
                            if ($storekeeper) {
                                $first_user_id = $storekeeper->id;
                                $second_user_id = $storekeeper->first_user_id;
                            }
                        }

                        $user->openid = $openid;

                        $user->user_type = 0;

                        $user->first_user_id = $first_user_id;

                        $user->second_user_id = $second_user_id;

                        $user->store_id = $this->store_id;

                        $user->super_id = $first_user_id;

                        $user->nickname = data_get($data, 'nickname');

                        $user->avatar = data_get($data, 'headimgurl');

                        $user->sex = data_get($data, 'sex');

                        $user->reg_time = $now;

                        $user->last_login = $now;

                        $user->last_ip = $_SERVER['REMOTE_ADDR'];

                        $user->province = data_get($data, 'province');

                        $user->city = data_get($data, 'city');

                        $user->country = data_get($data, 'country');

                        $user->subscribe = data_get($data,'subscribe');

                        $user->save();


                        //二维码生成
                        $url = config('AD_URL', 'https://gzchenyu.cn/advertisement') . '?store_id=' . $this->store_id . '&first_user_id=' . $user->id;

                        $user->qrcode_path = $this->generateQrcode($url);

                        $user->save();

                        $token = $this->userInfoSaveCache($user->id);
                        $mobile_bing = 0;
                        if ($user->mobile) {
                            $mobile_bing = 1;
                        }
                        $is_card = 0;
                        if ($user->card_id) {
                            $is_card = 1;
                        }
                        if ($token) {
                            $date = [
                                'token' => $token,
                                'is_mobile' => $mobile_bing,
                                'is_card' => $is_card,
                                'store_id' => $this->store_id
                            ];
                            return jsonSuccess($date);
                        }
                        return jsonError('token存储失败');
                    }

                } catch (\Exception $exception) {
                    Log::info($exception);
                    return jsonError('登录失败');
                }
            }
            return jsonError('用户信息获取失败', 20002);
        }
        return jsonError('授权失败');
    }

    //获取用户信息
    public function getWxInfo($code)
    {

        $codeUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->app_id . '&secret=' . $this->app_secret . '&code=' . $code . '&grant_type=authorization_code';
        //初始化curl
        try {
            $ch = curl_init();
            //需要获取的URL地址
            curl_setopt($ch, CURLOPT_URL, $codeUrl);
            //设置header
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $TokenData = curl_exec($ch);
            curl_close($ch);
            Log::info($TokenData);
            $TokenArr = json_decode($TokenData,true);
            $tokenAccess = data_get($TokenArr, 'access_token');
            $tokenOpenid = data_get($TokenArr, 'openid');
            if ($tokenAccess && $tokenOpenid) {
                $lang = 'zh_CN';
                //通过AccessToken 获取用户信息
                $userUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $tokenAccess . '&openid=' . $tokenOpenid . '&lang=' . $lang . '';
                //            $userUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $tokenAccess . '&openid=' . $tokenOpenid . '&lang=' . $lang . '';
                //初始化curl
                $ch = curl_init();
                //需要获取的URL地址
                curl_setopt($ch, CURLOPT_URL, $userUrl);
                //设置header
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $data = curl_exec($ch);
                curl_close($ch);
                Log::info($data);
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            Log::info($exception);
            return false;
        }

        $wx_info = json_decode($data,true);
        if (data_get($wx_info, 'openid')) {
            $result = $this->getSubscribe($tokenOpenid, $lang);
            $subscribe = data_get($result,'subscribe',0);
            $wx_info['subscribe'] = $subscribe;
            return $wx_info;
        }
        return false;
    }

    //判断用户是否关注公众号
    public function getSubscribe($tokenOpenid, $lang)
    {
        $res = $this->getAccessToken();
//            $tokenAccess = data_get($TokenArr,'access_token');
        $tokenAccess = data_get($res, 'accessToken');
        $userUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $tokenAccess . '&openid=' . $tokenOpenid . '&lang=' . $lang . '';
        $ch = curl_init();
        //需要获取的URL地址
        curl_setopt($ch, CURLOPT_URL, $userUrl);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
        curl_close($ch);
        $wx_info = json_decode($data,true);
        return $wx_info;
    }


    // 获取一键关注授权标识
    public function getIdentification()
    {
        $burl = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=" . $this->access_tokens . "";
        $result = curl_get($burl);
        preg_match('/__biz.*&mid/', $result, $matches);//正则截取字符串
        $sVid = $this->get_between($matches[0], "__biz=", "==&mid");//截取出微信公众号唯一标识
        $okurl = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=" . $sVid . "==&scene=124#wechat_redirect";
    }


    public function get_between($input, $start, $end)
    {
        $substr = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
        return $substr;
    }

    //获取access_token
    public function getAccessToken()
    {
        // access_token 应该全局存储与更新
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->app_id . "&secret=" . $this->app_secret;
        // 微信返回的信息
        $returnData = json_decode($this->httpGet($url),true);

        if ($returnData) {
            // 组装数据
            $resData['accessToken'] = data_get($returnData, 'access_token');
            $resData['expiresIn'] = data_get($returnData, 'expires_in');
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


    public function getJsApiTicket($accessToken)
    {
        // jsapi_ticket 应该全局存储与更新
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $accessToken . "&type=jsapi";
        // 微信返回的信息
        $returnData = json_decode($this->httpGet($url),true);
        // 组装数据
        $res = data_get($returnData, 'ticket');
        if ($res) {
            return $res;
        }
        return false;
    }

    // 获取签名
    public function getSignPackage()
    {
        if (!$this->store_id) {
            return jsonError('没有获取到店铺ID');
        }
        if (!$this->app_id) {
            return jsonError('没有获取APP_ID');
        }
        if (!$this->app_secret) {
            return jsonError('没有获取到APP_Secret');
        }
        // 获取token
        $token = $this->getAccessToken();
        // 获取ticket
        if ($token) {
            $ticket = $this->getJsApiTicket($token['accessToken']);
            if ($ticket) {
                // 该URL为使用JSSDK接口的URL

                // 时间戳
                $timestamp = strtotime($token['time']);
                // 随机字符串
                $nonceStr = $this->createNoncestr();
                // 这里参数的顺序要按照 key 值 ASCII 码升序排序 j -> n -> t -> u
                $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$this->url";
                $signature = sha1($string);
                $signPackage = array(
                    "appId" => $this->app_id,
                    "nonceStr" => $nonceStr,
                    "timestamp" => $timestamp,
                    "url" => $this->url,
                    "signature" => $signature,
                    "rawString" => $string,
                    "ticket" => $ticket,
                    "token" => $token['accessToken']
                );
                // 提供数据给前端
                return jsonSuccess($signPackage);
            }
            return jsonError('ticket获取失败');
        }
        return jsonError('access_token获取失败');
    }

    // 创建随机字符串
    private function createNoncestr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //微信SDK上传图片
    public function uploadImage(Request $request)
    {
        $localData = $request->input('localData');
        $url = explode(',', $localData);
        $targetName = "./demoUploads/" . date("YmdHis") . rand(1000, 9999) . ".jpg";
        file_put_contents($targetName, base64_decode($url[1]));//返回的是字节数
        if (file_exists($targetName)) {
            echo json_encode(['code' => '0001', 'localData' => $localData], JSON_UNESCAPED_UNICODE);
            exit(0);
        } else {
            echo json_encode(['code' => '0002', 'localData' => $localData], JSON_UNESCAPED_UNICODE);
            exit(0);
        }
    }
}