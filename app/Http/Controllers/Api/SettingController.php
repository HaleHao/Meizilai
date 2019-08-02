<?php

namespace App\Http\Controllers\Api;

use App\Models\UserAddress;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends CommonController
{
    //设置列表
    public function index(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }


        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $user_id]);
        $data = [
            'id' => $user_id,
            'avatar' => $user->avatar,
            'nickname' => $user->nickname,
            'username' => $user->username,
            'sex' => $user->sex,
            'birthday' => $user->birthday,
            'mobile' => $user->mobile,
        ];
        return jsonSuccess($data);
    }

    //设置
    public function settingSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }


        $username = $request->input('username');
        $nickname = $request->input('nickname');
        $sex = $request->input('sex');
        $birthday = $request->input('birthday');
        $user = Users::where('id',$user_id)->select(['id','username','nickname','sex','birthday','mobile','avatar'])->first();
        if ($username){
            $user->username = $username;
        }
        if ($nickname){
            $user->nickname = $nickname;
        }
        if ($sex){
            $user->sex = $sex;
        }
        if ($birthday != 'null'){
            $user->birthday = $birthday;
        }
        $user->save();
        return jsonSuccess($user);
    }

    //地址列表
    public function addressList(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $address = UserAddress::where('user_id',$user_id)->get();
        return jsonSuccess($address);
    }
}
