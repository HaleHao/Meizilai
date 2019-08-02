<?php

namespace App\Http\Controllers\Api;

use App\Models\Lorder;
use App\Models\MemberCard;
use App\Models\MemberLevel;
use App\Models\PayLog;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LevelController extends CommonController
{
    public function levelIndex(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->with('card')->first();
        $is_card = 0;
        if ($user->card_id) {
            $is_card = 1;
        }
        $card_id = $user->card_id;
//        $card = MemberCard::where('id', $card_id)->first();
//        if (!data_get($card,'level')) {
//            return jsonError($card->name . '不能申请升级等级');
//        }
        $level = MemberLevel::where('card_id', $card_id)->first();
        if (!$level){
            return jsonError('体验卡不能申请升级等级');
        }
        $data = [
            'user' => [
                'id' => $user_id,
                'username' => $user->username,
                'mobile' => $user->mobile,
                'reg_time' => date('Y-m-d', $user->reg_time),
                'card' => $user->card,
            ],

            'level' => $level,
            'is_card' => $is_card
        ];
        return jsonSuccess($data);
    }

    public function levelSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $level_id = $request->input('level_id');

        $user = Users::where('id', $user_id)->first();
        if (!$user) {
            return jsonLoginError();
        }

        $level = MemberLevel::where('id', $level_id)->first();
        if (!$level) {
            return jsonError('获取数据失败');
        }
        if ($user->level_id == $level_id) {
            return jsonError('您已经是该等级了，无需申请');
        }

        $res = Lorder::where('user_id',$user_id)->where('order_status',1)->where('pay_status',1)->first();

        if ($res){
            return jsonError('您提交过等级申请，请耐心等待审核');
        }

        $order = new Lorder();
        $order->level_id = $level_id;
        $order->user_id = $user_id;
        $order->level_name = $level->name;
        $order->store_id = $user->store_id;
        $order->order_sn = $this->getOrderSn();
        $order->username = $user->username;
        $order->mobile = $user->mobile;
        $order->order_status = 0;
        $order->pay_status = 0;
        $order->apply_status = 0;
        $order->total_price = $level->price;
        $order->submit_time = time();
        $result = $order->save();
        $data = [
            'order_id' => $order->id
        ];

        if ($result) {
            return jsonSuccess($data, '订单生成成功');
        }
        return jsonError('订单生成失败');
    }


    public function levelPay(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        if ($order_id) {
            $orderModel = new Lorder();
            $order = $orderModel->where('user_id', $user_id)->where('id', $order_id)->first();
            if ($order) {
                if ($order->order_status == 0 && $order->pay_status == 0) {
                    //支付订单
                    $userModel = new Users();
                    $user = $userModel->getInfo(['id' => $user_id]);
                    $order_sn = data_get($order, 'order_sn');
                    $openid = data_get($user, 'openid');
                    $total_fee = data_get($order, 'total_price') * 100;
                    $spbill_create_ip = $this->get_client_ip();

                    //回调路径
                    $url = url('api/v1/level/notify');

                    $payController = new WechatPayController($user->store_id);
                    $body = "魅资莱会员升级";
                    $result = $payController->ordering($order_sn, $total_fee, $spbill_create_ip, $order_id, $openid, $url, $body);
                    if ($result) {
                        return jsonSuccess($result);
                    }
                    return jsonError('支付失败');
                }
                return jsonError('该订单不能支付');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');
    }

    public function levelNotify()
    {
        $data = file_get_contents("php://input");
//        Log::info(json_encode($data));
//        $wachatPay = new WechatPayController();
        $data = $this->XmlToArr($data);
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                $orderCode = $data['out_trade_no'];
                $total_fee = $data['total_fee'];
                $total_fee = $total_fee / 100;
                try {
                    DB::beginTransaction();
                    $order = Lorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->first();
                    if ($order) {

                        $order->pay_status = 1;
                        $order->order_status = 1;
                        $order->pay_sn = $data['transaction_id'];
                        $order->pay_time = time();
                        $order->save();

                        $user = Users::where('id', $order->user_id)->first();

                        $pay_log = new PayLog();
                        $pay_log->user_id = $order->user_id;
                        $pay_log->nickname = $user->nickname;
                        $pay_log->order_id = $order->id;
                        $pay_log->content = $user->nickname . "于" . date('Y-m-d H:i:s') . "申请购买了" . $order->level_name;
                        $pay_log->event_type = 'card';
                        $pay_log->happen_time = time();
                        $pay_log->save();

                        $arr = [];
                        $arr['return_code'] = 'SUCCESS';
                        $arr['return_msg'] = 'OK';
                        $return = $this->arrayToXml($arr);
                        echo $return;
                        DB::commit();

                    } else {
                        $arr = [];
                        $arr['return_code'] = 'FAIL';
//                        $arr['return_msg'] = 'OK';
                        $return = $this->arrayToXml($arr);
                        echo $return;
                    }
                } catch (\Exception $exception) {
                    Log::info($exception);

                    $arr = [];
                    $arr['return_code'] = 'FAIL';
//                        $arr['return_msg'] = 'OK';
                    $return = $this->arrayToXml($arr);
                    echo $return;
                    DB::rollBack();
                }
            }
        }
    }
}
