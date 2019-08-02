<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CardList;
use App\Http\Resources\CartList;
use App\Http\Resources\UsersList;
use App\Models\MemberCard;
use App\Models\ShopCart;
use App\Models\Users;
use App\Service\PosterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Mrgoon\AliSms\AliSms;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;

class UsersController extends CommonController
{

    //微信登录后 进行手机号绑定注册
    public function mobileBinding(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->first();
        if (!$user) {
            return jsonLoginError();
        }

        $username = $request->input('username');
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        if (!$username) {
            return jsonError('请填写姓名');
        }
        if (!$mobile) {
            return jsonError('请填写手机号码');
        }
        if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
            return jsonError("请输入正确的手机号码");
        }

        if (!$code) {
            return jsonError('请填写短信验证码');
        }

        $userModel = new Users();
        $res = $userModel->getInfo(['mobile' => $mobile]);
        if ($res) {
            return jsonError('该手机号码已被注册');
        }

        $cache_code = Cache::get($mobile);
        if (!$cache_code) {
            return jsonError('请获取验证或者验证码已过期');
        }
        if ($cache_code != $code) {
            return jsonError('验证码不正确');
        }

        $user = $userModel->where('id', $user_id)->first();
        $user->mobile = $mobile;
        $user->username = $username;
        $result = $user->save();
        $is_card = $user->card_id ? 1 : 0;
        $data = [
            'is_card' => $is_card
        ];
        if ($result) {
            return jsonSuccess($data, '注册成功');
        }
        return jsonError('注册失败');

    }

    //手机登录
    public function mobileLogin(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $mobile = $request->input('mobile');
        if (!$mobile) {
            return jsonError('请填写手机号码');
        }
        if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
            return jsonError("请输入正确的手机号码");
        }

        $code = $request->input('code');
        if (!$code) {
            return jsonError('请填写验证码');
        }
        $cache_code = Cache::get($mobile);
        if (!$cache_code) {
            return jsonError('获取验证码或验证码已过期');
        }
        if ($cache_code != $code) {
            return jsonError('验证码不正确');
        }
        $userModel = new Users();
        $user = $userModel->where('mobile', $mobile)->where('id', $user_id)->first();
        if ($user) {
            $is_card = $user->card_id ? 1 : 0;
            $data = [
                'is_card' => $is_card
            ];
            return jsonSuccess($data, '登录成功');
        }
        return jsonError('登录失败');
    }

    //发送短信
    public function sendCode(Request $request)
    {
//        $user_id = ;
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $mobile = $request->input('mobile');

        if (!$mobile) {
            return jsonError('请填写手机号码', 20001);
        }
        if (!preg_match("/^1[345678]{1}\d{9}$/", $mobile)) {
            return jsonError("请输入正确的手机号码");
        }
        $type = $request->input('type', 1);
        if ($type == 1) {
            $tmp_id = 'SMS_163053005';
        } else {
            $tmp_id = 'SMS_163057817';
        }
        $code = rand(100000, 999999);
        $aliSms = new AliSms();
        $result = $aliSms->sendSms($mobile, $tmp_id, ['code' => $code]);

        $result = data_get($result, 'Message');
        Log::info('SMS-INFO:'.$result);
        if ($result == "OK") {
            //将数据存放在缓存
            Cache::put($mobile, $code, 2);
            return jsonMsg('发送成功');
        }
        return jsonError('发送失败');
    }


    //个人中心
    public function my(Request $request)
    {
        //获取用户信息
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $userModel = new Users();
        $user = $userModel->where('id', $user_id)->first(['id', 'card_id', 'earnings', 'withdraw', 'avatar', 'username', 'nickname', 'avatar', 'user_type', 'level_id', 'card_num', 'expiration_time']);
        $card = MemberCard::where('id', $user->card_id)->first();
        $member_card = [];
        $is_card = 0;
        if ($card) {
            $member_card = CardList::make($card);
            $is_card = 1;
        }
        $data = [
            'card' => $member_card,
            'user' => $user,
            'is_card' => $is_card,
            'user_id' => $user_id
        ];

        return jsonSuccess($data);
    }

    //我的海报
    public function poster(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->with('store')->first();

        if (!$user) {
            return jsonLoginError();
        }

        if (!$user->poster) {

            $storekeeper = Users::where('store_id', $user->store_id)->where('user_type', 4)->where('is_storekeeper', 1)->with('level')->with('card')->first();

            $img = Image::make('uploads/poster/poster.png')->resize('375', '667');

            $qrcode = Image::make('uploads/' . $user->qrcode_path)->resize(100, 100);

            $img->insert($qrcode, 'bottom-left', 80, 20);

            $img->text(data_get($storekeeper, 'username') ? '店主:' . data_get($storekeeper, 'username') : '店主:' . '暂无', 190, 560, function ($font) {

                $font->file('ttf/WeiRuanYaHei.ttf');

                $font->size(12);

//            $font->align('center');

                $font->valign('bottom');

                $font->color('#000');
            });
            $img->text(data_get($storekeeper, 'id') ? '店主编号:' . data_get($storekeeper, 'id') : '店主编号:' . '暂无', 190, 580, function ($font) {

                $font->file('ttf/WeiRuanYaHei.ttf');

                $font->size(12);

//            $font->align('center');

                $font->valign('bottom');

                $font->color('#000');
            });
            $img->text('店铺编号:' . $user->store->id, 190, 600, function ($font) {

                $font->file('ttf/WeiRuanYaHei.ttf');

                $font->size(12);

//            $font->align('center');

                $font->valign('bottom');

                $font->color('#000');
            });
            $img->text(data_get(data_get($storekeeper, 'level'), 'name') ? '店主等级:' . data_get(data_get($storekeeper, 'level'), 'name') : '店主等级:' . '暂无', 190, 620, function ($font) {

                $font->file('ttf/WeiRuanYaHei.ttf');

                $font->size(12);

//            $font->align('center');

                $font->valign('bottom');

                $font->color('#000');
            });
            $img->text(data_get(data_get($storekeeper, 'card'), 'name') ? '会员卡等级:' . data_get(data_get($storekeeper, 'card'), 'name') : '会员卡等级:' . '暂无', 190, 640, function ($font) {

                $font->file('ttf/WeiRuanYaHei.ttf');

                $font->size(12);

//            $font->align('center');

                $font->valign('bottom');

                $font->color('#000');
            });
// create a new Image instance for inserting
            $token = date('YmdHis') . substr(md5(time()), 0, 8) . '.jpg';
            $path = 'uploads/poster/' . $token;
            $filename = 'poster/' . $token;
            $img->save($path);

            $user->poster = $filename;
            $user->save();

        }
        $poster = url('uploads/' . $user->poster);
        return jsonSuccess($poster);

    }

    //我的团队
    public function team(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $type = $request->input('type');
        if ($type == 1) {
            $user = Users::where('first_user_id', $user_id)->where('level_id', 2)->select(['id', 'avatar', 'nickname', 'reg_time'])->get();
        } else {
            $user = Users::where('first_user_id', $user_id)->where('level_id', 1)->select(['id', 'avatar', 'nickname', 'reg_time'])->get();
        }
        $storekeeper_num = Users::where('first_user_id', $user_id)->where('level_id', 2)->count('id');
        $angel_num = Users::where('first_user_id', $user_id)->where('level_id', 1)->count('id');

        $list = [];
        if ($user) {
            $list = UsersList::collection($user);
        }
        $data = [
            'list' => $list,
            'angel_num' => $angel_num,
            'storekeeper_num' => $storekeeper_num,
        ];
        return jsonSuccess($data);
    }

}
