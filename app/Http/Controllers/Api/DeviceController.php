<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ServeDetail;
use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\Device;
use App\Models\DeviceLog;
use App\Models\Dorder;
use App\Models\EarningsLog;
use App\Models\Sign;
use App\Models\PayLog;
use App\Models\Sorder;
use App\Models\Users;
use App\Service\MqttService;
use App\Service\phpMQTT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceController extends CommonController
{
    //扫码主页
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->first();
        if (!$user) {
            return jsonLoginError();
        }
        $device_id = $request->input('device_id');
        if (!$device_id) {
            return jsonError('参数获取失败');
        }

        if (!$user->card_id) {
            return jsonError('您还没有购买会员卡');
        }

        if ($user->card_num <= 0) {
            return jsonError('您的会员卡没有次数了，请充值');
        }


        $device = Device::where('id', $device_id)->first();
        if (!$device) {
            return jsonError('没有获取到设备信息');
        }

        //判断是否服务时间段
        $start_time = strtotime("-20 minute");
        $end_time = strtotime("+20 minute");
        $serve_order = Sorder::where('user_id', $user_id)
            ->whereBetween('serve_time', [$start_time, $end_time])
            ->where('pay_status', 1)
            ->where('order_status',2)
            ->with('orderServe')->first();
        $detail = [];
        $arr = [];
        if ($serve_order) {
            $detail = ServeDetail::make($serve_order);
            $is_serve = 1;
        }else{
            //没有则选择美容师直接开机
            $date = date('Y-m-d');
            $sign = Sign::where('store_id', $user->store_id)->where('sign_date', $date)->get();
            foreach ($sign as $value) {
                $beautician = Users::where('id', $value->user_id)
                    ->where('store_id', $user->store_id)
                    ->where('is_beautician', 1)
                    ->where('user_type', 3)
                    ->select(['id', 'username', 'avatar', 'serve_num','grade'])
                    ->first();
                if ($beautician) {
                    $arr[] = $beautician;
                }
            }
            $is_serve = 0;
        }


        $data = [
            'total_price' => config('Boot_Fee', 9.9),
            'device_id' => $device_id,
            'store_id' => $user->store_id,
            'serve_order' => $detail,
            'beautician' => $arr,
            'is_serve' => $is_serve
        ];
        return jsonSuccess($data);

    }

    //设备订单提交
    public function submit(Request $request)
    {

        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->first();
        if (!$user) {
            return jsonLoginError();
        }

        $device_id = $request->input('device_id');
        if (!$device_id) {
            return jsonError('参数获取失败');
        }
        $device = Device::where('id', $device_id)->first();
        if (!$device) {
            return jsonError('设备获取失败');
        }

        if (!$user->card_id) {
            return jsonError('您还没有购买会员卡', 20002);
        }

        if ($user->card_num <= 0) {
            return jsonError('您的会员卡没有次数了，请充值', 20002);
        }

        //判断是否服务时间段
//        $start_time = strtotime("-20 minute");
//        $end_time = strtotime("+20 minute");
//        $serve_order = Sorder::where('user_id', $user_id)->whereBetween('serve_time', [$start_time, $end_time])->where('pay_status', 1)->where('order_status',2)->first();
//        if (!$serve_order) {
//            return jsonError('你在现时间区间没有预约服务', 20003);
//        }
//        if ($serve_order->order_status != 2) {
//            return jsonError('美容师还没有接单或取消订单了', 20003);
//        }

        $is_serve = $request->input('is_serve');
        $serve_id = $request->input('serve_id');
        if ($is_serve == 1 && $serve_id){
            $serve_order = Sorder::where('user_id',$user_id)->where('id',$serve_id)->first();
            $beautician_id = $serve_order->beautician_id;
            $serve_id = $serve_order->id;
        }else{
            $beautician_id = $request->input('beautician_id');
            $serve_id = 0;
        }

        if (!$beautician_id){
            return jsonError('请选择美容师');
        }

        $total_price = config('Boot_Fee', 9.9);
        $order = new Dorder();
        $order->store_id = $user->store_id;
        $order->device_id = $device_id;
        $order->user_id = $user_id;
        $order->beautician_id = $beautician_id;
        $order->serve_order_id = $serve_id;
        $order->order_sn = $this->getOrderSn();
        $order->order_status = 0;
        $order->pay_status = 0;
        $order->total_price = $total_price;
        $order->beautician_price = $total_price * config('Beautician_Ratio', 0.9);
        $order->company_price = $total_price * config('Company_Ratio', 0.1);
        $order->submit_time = time();
        $result = $order->save();
        if ($result) {
            return jsonSuccess($order, '订单生成成功');
        }
        return jsonError('订单生成失败');
    }

    //设备订单支付
    public function pay(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $user = Users::where('id', $user_id)->first();
        if (!$user) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('参数获取失败');
        }

        $order = Dorder::where('id', $order_id)->first();
        if (!$order) {
            return jsonError('订单获取失败');
        }
        if ($order->order_status != 0 || $order->pay_status != 0) {
            return jsonError('该订单不能支付');
        }
        $order_sn = data_get($order, 'order_sn');
        $openid = data_get($user, 'openid');
        $total_fee = data_get($order, 'total_price') * 100;
        $spbill_create_ip = $this->get_client_ip();

        //回调路径
        $url = url('api/v1/device/notify');
        $payController = new WechatPayController($user->store_id);
        $body = "魅资莱设备使用";
        $result = $payController->ordering($order_sn, $total_fee, $spbill_create_ip, $order_id, $openid, $url, $body);

        if ($result) {
            return jsonSuccess($result);
        }
        return jsonError('支付失败');
    }

    //支付回调
    public function notify()
    {
        $data = file_get_contents("php://input");
        Log::info(json_encode($data));
//        $wachatPay = new WechatPayController();
        $data = $this->XmlToArr($data);
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                $orderCode = $data['out_trade_no'];
                $total_fee = $data['total_fee'];
                $total_fee = $total_fee / 100;
                try {
                    DB::beginTransaction();
                    $order = Dorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->lockForUpdate()->first();
                    if ($order) {

                        //修改订单状态
                        $order->order_status = 1;
                        $order->pay_status = 1;
                        $order->pay_sn = $data['transaction_id'];
                        $order->pay_time = time();
                        $order->save();

                        //修改用户会员卡使用次数,使用次数减1
                        $user = Users::where('id', $order->user_id)->first();
                        $user->card_num = $user->card_num - 1;
                        $user->save();

                        //获取设备
                        $device = Device::where('id', $order->device_id)->first();

                        //保存购买记录
                        $pay_log = new PayLog();
                        $pay_log->user_id = $order->user_id;
                        $pay_log->nickname = $user->nickname;
                        $pay_log->order_id = $order->id;
                        $pay_log->content = $user->nickname . "于" . date('Y-m-d H:i:s') . "开启[" . $device->device_name . "]设备，设备编号：[" . $device->device_sn . "]";
                        $pay_log->event_type = 'device';
                        $pay_log->happen_time = time();
                        $pay_log->save();

                        //设备开启
                        $this->openDevice($device->device_sn);

                        //保存设备日志
                        $device_log = new DeviceLog();
                        $device_log->device_id = $device->id;
                        $device_log->store_id = $order->store_id;
                        $device_log->user_id = $user->id;
                        $device_log->content = $user->nickname . "于" . date('Y-m-d H:i:s') . "开启[" . $device->device_name . "]设备";
                        $device_log->open_type = 1;
                        $device_log->device_minute = $device->device_minute;
                        $device_log->open_time = time();
                        $device_log->close_time = time() + ($device->device_minute * 60);
                        $device_log->send_time = time();
                        $device_log->save();

                        //将分销信息保存
                        $beautician = Users::where('id', $order->beautician_id)->first();
                        //保存金额分销金额到美容师
                        $beautician->earnings = $beautician->earnings + $order->beautician_price;
                        $beautician->serve_num = $beautician->serve_num + 1;
                        $beautician->save();

                        //保存美容师收益记录
                        $EarningsLog = new EarningsLog();
                        $EarningsLog->event_name = "美容服务-" . $user->username;
                        $EarningsLog->transaction_type = 1;
                        $EarningsLog->store_id = $order->store_id;
                        $EarningsLog->user_id = $beautician->id;
                        $EarningsLog->add_time = time();
                        $EarningsLog->add_date = date('Y-m-d');
                        $EarningsLog->order_id = $order->id;
                        $EarningsLog->event_type = 'serve';
                        $EarningsLog->earnings_amount = $order->beautician_price;
                        $EarningsLog->order_amount = $order->total_price;
                        $EarningsLog->save();

                        //保存公司得收益记录
                        $company = Company::orderBy('created_at', 'desc')->first();
                        $company_log = new CompanyLog();
                        $company_log->company_id = $company->id;
                        $company_log->store_id = $order->store_id;
                        $company_log->beautician_id = $order->beautician_id;
                        $company_log->user_id = $order->user_id;
                        $company_log->order_id = $order->id;
                        $company_log->earnings_amount = $order->company_price;
                        $company_log->withdraw_amount = $order->company_price;
                        $EarningsLog->order_amount = $order->total_price;
                        $company_log->withdraw_type = 0;
                        $company_log->add_time = time();
                        $company_log->add_date = date('Y-m-d');
                        $company_log->save();

                        //增加公司收益
                        $company->earnings = $company->earnings + $order->company_price;
                        $company->save();

                        //修改服务订单状态
                        if($order->serve_order_id){
                            $serve_order = Sorder::where('id', $order->serve_order_id)->first();
                            $serve_order->order_status = 5;
                            $serve_order->save();
                            //对服务订单进行退款
                            $payModel = new WechatPayController($serve_order->store_id);
                            $transaction_id = $serve_order->pay_sn;
                            $refund_no = $serve_order->order_sn;
                            $total_fee = $serve_order->total_price * 100;
                            $refund_desc = '预约服务金额返还';
                            $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
                        }


                        DB::commit();
                        $arr = [];
                        $arr['return_code'] = 'SUCCESS';
                        $arr['return_msg'] = 'OK';
                        $return = $this->arrayToXml($arr);
                        echo $return;
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
                    DB::rollBack();
                    $arr = [];
                    $arr['return_code'] = 'FAIL';
//                        $arr['return_msg'] = 'OK';
                    $return = $this->arrayToXml($arr);
                    echo $return;
                }
            }
        }
    }


    public function openDevice($device_sn)
    {
        //phpMQTT 有四个参数：主机，端口，客户端id，证书。官网这里的案例没写证书，请参考phpMQTT类
        //没有证书的时候只能连接1883端口，不能连接8883端口。

        $host = config("MQTT_HOST", "47.104.12.54"); //主机
        $port = config("MQTT_PORT", "4883"); //端口
        $username = config("MQTT_APPID", "6b68c250a1264025b7a546e0c66ad834");; //如果没有则为空
        $password = config("MQTT_Secret", "3a6d44d6b62c4a95afc2efad5348a989"); //如果没有则为空
        //获取设备,开启机器
        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接机器
        $mqtt->connect(true, NULL, $username, $password);
        //订阅机器
        Log::info("6b68c250a1264025b7a546e0c66ad834/" . $device_sn);
        $device_id = "6b68c250a1264025b7a546e0c66ad834/" . $device_sn;
        $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
        $mqtt->subscribe($topics, 0);
        //发送消息

        $device_control = config('global.device.device_control.open','EC 08 04 01 00 00 00 00 01 EF');
        $new_control = 'EC 08 04 01 00 00 00 00 0D EF';
        $relay_control = config('global.device.relay_control.open','10 04 01');

        $sound = config('global.device.sound_control.open','10 05 01 20');

        //转为16进制数据
        $device_control = hex2bin(preg_replace('# #', '', $device_control));
        $new_control = hex2bin(preg_replace('# #', '', $new_control));
        $relay_control = hex2bin(preg_replace('# #', '', $relay_control));
        $soundHEX = hex2bin(preg_replace('# #', '', $sound));

        $mqtt->publish($device_id, $device_control, 0);

        $mqtt->publish($device_id, $new_control, 0);

        $mqtt->publish($device_id, $relay_control, 0);

        $mqtt->publish($device_id, $soundHEX, 0);
        //关闭连接
        $mqtt->close();

    }
}
