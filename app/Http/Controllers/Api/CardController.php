<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CardList;
use App\Logic\OrderLogic;
use App\Models\Corder;
use App\Models\MemberCard;
use App\Models\OrderCard;
use App\Models\PayLog;
use App\Models\Users;
use EasyWeChat\Kernel\Messages\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardController extends CommonController
{


    //会员卡列表
    public function cardList(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $user = Users::where('id',$user_id)->first();
        if(!$user){
            return jsonLoginError();
        }

        $cardModel = new MemberCard();

        $user_card = $cardModel->getDetail(['id' => $user->card_id]);
        //看用户是否之前购买会员，如果购买了，则显示比该会员卡等级高的会员卡
//        if ($user_card){
//            $where[] = ['level','>=' , $user_card->level];
//        }
//        $where[] = ['is_show',1];
//        $card = $cardModel->getList($where);
//
////        array_merge($card,);
        if ($user_card){
            $cardModel = $cardModel->where('level','>=' , $user_card->level);
            if (config('card_switch','on') == 'on'){
                $cardModel = $cardModel->orWhere('level',0);
            }
        }

        $card = $cardModel->orderBy('sort','desc')->get();
        $list = [];
        if ($card){
            $list = CardList::collection($card);
        }
        return jsonSuccess($list);
    }

    //选择会员卡后提交，生成订单
    public function cardSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $card_id = $request->input('card_id');
        if ($card_id){
            $cardModel = new MemberCard();
            $where = [
                'id' => $card_id
            ];
            $card = $cardModel->getDetail($where);
            if ($card){
                DB::beginTransaction();
                try{
                    $userModel = new Users();
                    $where = [
                        'id' => $user_id
                    ];
                    $user = $userModel->getInfo($where);
                    $card_price = $card->mall_price;
                    $order_arr = [
                        'order_sn' => $this->getOrderSn(),
                        'order_status' => 0,
                        'pay_status' => 0,
                        'card_id' => $card_id,
                        'total_price' => $card_price,
                        'user_id' => $user_id,
                        'submit_time' => time(),
                        'store_id' => $user->store_id,
                        'username' => $user->username,
                        'mobile' => $user->mobile
                    ];
                    $corderModel = new Corder();
                    $order = $corderModel->addOrder($order_arr);
                    $card_arr = [
                        'order_id' => $order->id,
                        'user_id' => $user_id,
                        'card_id' => $card_id,
                        'card_price' => $card_price,
                        'submit_time' => time(),
                    ];
                    $orderCardModel = new OrderCard();
                    $orderCardModel->addCard($card_arr);
                    DB::commit();
                    return jsonSuccess($order,'生成订单成功');
                }catch (\Exception $exception){
                    Log::info($exception);
                    DB::rollBack();
                    return jsonError('生成订单失败');
                }
            }
            return jsonError('会员卡获取失败');
        }
        return jsonError('参数错误');
    }

    //订单支付
    public function cardPay(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $userModel = new Users();
        $user = $userModel->getInfo(['id'=> $user_id]);
        if (!$user){
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        if ($order_id){
            $orderModel = new Corder();
            $where = [
                'order_status' => 0,
                'pay_status' => 0,
                'id' => $order_id
            ];
            $order = $orderModel->getDetail($where);
            if ($order){

                $order_sn = data_get($order, 'order_sn');
                $openid = data_get($user, 'openid');
                $total_fee = data_get($order, 'total_price') * 100;
                $spbill_create_ip = $this->get_client_ip();

                //回调路径
                $url = url('api/v1/card/notify');

                $payController = new WechatPayController($order->store_id);
                $body = "魅资莱会员卡购买";
                $result = $payController->ordering($order_sn,$total_fee,$spbill_create_ip,$order_id,$openid,$url,$body);
                if ($result){
                    return jsonSuccess($result);
                }
                return jsonError('支付失败');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');
    }

    //支付回调
    public function cardNotify()
    {
        $data = file_get_contents("php://input");
//        Log::info(json_encode($data));
//        $wachatPay = new WechatPayController();
        $data = $this->XmlToArr($data);
        Log::info($data);
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                $orderCode = $data['out_trade_no'];
                $total_fee = $data['total_fee'];
                $total_fee = $total_fee / 100;
                try {
                    DB::beginTransaction();
                    $order = Corder::where('order_sn', $orderCode)->where('total_price', $total_fee)->lockForUpdate()->first();
                    if ($order) {

                        //修改订单状态
                        $order->order_status = 1;
                        $order->pay_status = 1;
                        $order->pay_sn = $data['transaction_id'];
                        $order->pay_time = time();
                        $order->save();

                        //会员卡出售数量+1
                        $card = MemberCard::where('id',$order->card_id)->first();
                        $card->sell_num = $card->sell_num + 1;
                        $card->save();

                        $user = Users::where('id',$order->user_id)->first();
                        $user->card_id = $order->card_id;
                        //会员类型
                        if ($user->user_type < 1){
                            $user->user_type = 1;
                        }
                        //用户会员卡使用次数
                        $user->card_num = $card->use_num;
                        //会员卡到期时间
                        $user->expiration_time = date('Y-m-d',strtotime('+1 year'));
                        $user->save();

                        $pay_log = new PayLog();
                        $pay_log->user_id = $order->user_id;
                        $pay_log->nickname = $user->nickname;
                        $pay_log->order_id = $order->id;
                        $pay_log->content = $user->nickname."于".date('Y-m-d H:i:s')."购买了".$card->name;
                        $pay_log->event_type = 'card';
                        $pay_log->happen_time = time();
                        $pay_log->save();

                        //处理分销信息
                        $inputData = [
                            'user_id' => $order->user_id,
                            'name' => '购买'.$card->name.'-'.$user->username,
                            'transaction_type' => 1,
                            'event_type' => 'card',
                            'store_id' => $order->store_id,
                            'order_id' => $order->id,
                            'order_amount' => $order->total_price,
                        ];
                        Log::info($inputData);
                        $orderLogic = new OrderLogic();
                        $orderLogic->save($inputData);

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
                        DB::rollBack();
                    }
                } catch (\Exception $exception) {
                    Log::info($exception);
                    $arr = [];
                    $arr['return_code'] = 'FAIL';
//                        $arr['return_msg'] = 'OK';
                    $return = $this->arrayToXml($arr);
                    echo $return;
                }
            }
        }
    }


}
