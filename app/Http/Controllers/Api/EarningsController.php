<?php

namespace App\Http\Controllers\Api;

use App\Models\EarningsLog;
use App\Models\Users;
use App\Models\WithdraLog;
use App\Models\WithdrawLog;
use Illuminate\Http\Request;

class EarningsController extends CommonController
{
    //
    //我的收益
    public function earningsMy(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->select(['id','avatar','nickname','username','earnings','withdraw'])->first();
        if (!$user){
            return jsonLoginError();
        }
        //收益记录
        $earnings = EarningsLog::where('user_id',$user_id)->orderBy('add_time','desc')->get();
        //提现记录
        $withdraw = WithdrawLog::where('user_id',$user_id)->orderBy('add_time','desc')->get();
        $data = [
            'user' => $user,
            'earnings' => $earnings,
            'withdraw' => $withdraw,
        ];
        return jsonSuccess($data);
    }


    //用户提现接口
    public function withdraw(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $user_id]);
        if (!$user){
            return jsonLoginError();
        }

        $amount = $request->input('amount');
        if (!$amount){
            return jsonError('请填写提现金额');
        }
        if ($amount > $user->earnings){
            return jsonError('提现金额不能大于收益');
        }


        $wechat = new WechatPayController($user->store_id);
        $partner_trade_no = date("YmdHis") . rand(10000, 90000) . rand(10000, 90000);
        $desc = $user->nickname.'收益提现';
        $result = $wechat->transfers($partner_trade_no,$user->openid,$amount,$desc);
        if (data_get($result,'status') == 1){

            //修改用户收益
            $user->eranings = $user->earnings - $amount;
            $user->withdraw = $user->withdraw + $amount;
            $user->save();

            //添加提现记录
            $withdraw = new WithdrawLog();
            $withdraw->event_name = '提现';
            $withdraw->store_id = $user->store_id;
            $withdraw->user_id = $user_id;
            $withdraw->add_time = time();
            $withdraw->witthdraw_amount = $amount;
            $withdraw->save();

            return jsonMsg('提现成功');

        }else{
            return jsonError(data_get($result,'msg'));
        }

    }


}
