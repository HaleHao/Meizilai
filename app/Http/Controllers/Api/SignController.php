<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SignList;
use App\Models\Sign;
use App\Models\StoreComment;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SignController extends CommonController
{
    //签到页面
    public function sign(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $time = $request->input('time',date('Y/m/d'));
        $user_type = $request->input('user_type');


        $userModel = new Users();
        $user = $userModel->getInfo(['id'=>$user_id]);
        $first_day = date('Y-m-01', strtotime($time));
        $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));
        $signModel = new Sign();
        $store_id = $user->store_id;

        $where['user_id'] = $user_id;

//        dd($user_type);
        //0:普通会员 1:会员 2:共享合伙人 3:美容师 4:店主
        switch ($user_type)
        {
            //普通会员
            case 0:
                $data = ['user_type' => $user_type,];
                break;
            //会员
            case 1:
                $data = ['user_type' => $user_type,];
                break;
            //共享合伙人
            case 2:
                $sign = Sign::where('user_id',$user_id)->whereBetween('sign_time',[strtotime($first_day),strtotime($last_day)])->get();
                $list = [];
                if ($sign){
                    $list = SignList::collection($sign);
                }
                $where['sign_date'] = date('Y-m-d',strtotime($time));
                $today_sign = $signModel->getSignDetail($where);
                $is_sign = 0;
                if ($today_sign){
                    $is_sign = 1;
                }
                $data = [
                    'list' => $list,
                    'day_num' => count($sign),
                    'is_sign' => $is_sign
                ];
                break;
            //美容师
            case 3:
                $sign = $signModel->getSignList($where,strtotime($first_day),strtotime($last_day));
                $list = [];
                if ($sign){
                    $list = SignList::collection($sign);
                }
                $where['sign_date'] = date('Y-m-d',strtotime($time));
                $sign = $signModel->getSignDetail($where);
                $is_sign = 0;
                if ($sign){
                    $is_sign = 1;
                }
                $data = [
                    'list' => $list,
                    'day_num' => count($sign),
                    'is_sign' => $is_sign
                ];
                break;
            //店主
            case 4:
                $where['sign_date'] = date('Y-m-d',strtotime($time));
                $sign = $signModel->getSignDetail($where);
                $is_sign = 0;
                if ($sign){
                    $is_sign = 1;
                }

                $begin_time = strtotime($first_day);
                $end_time = strtotime($last_day);
                //计算每天打卡的人数
                for ($start = $begin_time; $start <= $end_time; $start += 24 * 3600) {
                    $num_date = date('m-d',$start);
                    $date= date('Y-m-d',$start);
                    $day_num[$num_date] = $signModel->where('store_id',$store_id)->where('sign_date',$date)->count();
                }
                //计算未打卡人的数量
                $userList = $userModel->where('is_partner',1)->where('is_storekeeper',0)->where('store_id',$store_id)->get();
                $month_num = (int)date('d',$end_time);
                $total_sign = 0;
                $no_num = [];
                foreach ($userList as $value){
                    $num = $signModel->where('store_id',$store_id)->where('user_id',$value->id)->whereBetween('sign_date',[$first_day,$last_day])->count();
                    $no_num[] = [
                        'num' => $month_num - $num,
                        'avatar' => $value->avatar,
                        'nickname' => $value->nickname
                    ];
                    $total_sign += $num;
                }
                //计算比例
                $total_user = count($userList);

                $yes_ratio = (int)round(($total_sign/($total_user*$month_num))*100);

                $data = [
                    'is_sign' => $is_sign,
                    'day_num' => $day_num,
                    'no_num' => $no_num,
                    'yes_ratio' => $yes_ratio,
                    'no_ratio' => 100-$yes_ratio
                ];
                break;
        }
        return jsonSuccess($data);
    }

    //签到
    public function signSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $time = $request->input('time',date('Y/m/d'));
        $timestamp = strtotime($time);
        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $user_id]);
        $signModel = new Sign();
        if ($time){
            $where = [
                'sign_date' => date('Y-m-d',$timestamp),
                'user_id' => $user_id
            ];
            $result = $signModel->getSignDetail($where);
            if (!$result){
                $arr = [
                    'user_id' => $user_id,
                    'sign_time' => $timestamp,
                    'sign_date' => date('Y-m-d',$timestamp),
                    'store_id' => $user->store_id
                ];
                $result = $signModel->addSign($arr);
                if ($result){
                    $first_day = date('Y-m-01', $timestamp);
                    $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));
                    $where3 = [
                        'user_id' => $user_id,
                    ];
                    $sign = $signModel->getSignList($where3,strtotime($first_day),strtotime($last_day));
                    $list = [];
                    if ($sign){
                        $list = SignList::collection($sign);
                    }
                    $data = [
                        'list' => $list,
                        'is_sign' => 1
                    ];
                    return jsonSuccess($data,'签到成功');
                }
            }
            return jsonError('您今天今天已经签到过了');
        }
        return jsonError('签到失败');
    }

    //签到排行
    public function signRank(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }


        $time = $request->input('time',date('Y/m/d'));

        $first_day = date('Y-m-01', strtotime($time));
        $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));


        $userModel = new Users();
        $user = $userModel->getInfo(['id'=>$user_id]);
        $userList = $userModel->where('is_partner',1)->where('is_storekeeper',0)->where('store_id',$user->store_id)->get();

        $sign_rank = [];
        $signModel = new Sign();
        foreach ($userList as $value){
            $sign_num = $signModel->where('store_id',$user->store_id)->where('user_id',$value->id)->whereBetween('sign_date',[$first_day,$last_day])->count();
            $sign_rank[] = [
                'sign_num' => $sign_num,
                'avatar' => $value->avatar,
                'nickname' => $value->nickname
            ];
        }
        if ($sign_rank){
            $last_names = array_column($sign_rank,'sign_num');
            array_multisort($last_names,SORT_DESC,$sign_rank);
        }
        return jsonSuccess($sign_rank);
    }

    //评论排行
    public function commentRank(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $userModel = new Users();
        $user = $userModel->getInfo(['id'=>$user_id]);
        $store_id = $user->store_id;
        $where = [
            'store_id' => $store_id,
            'is_beautician' => 1
        ];
        $user_list = $userModel->getBeauticianList($where);
        if ($user_list){
            $data = $user_list->toArray();
            $grade = array_column($data,'grade');
            array_multisort($grade,SORT_DESC,$data);
        }
        return jsonSuccess($data);
    }

}
