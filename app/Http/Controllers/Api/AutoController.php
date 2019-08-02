<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Models\DeviceLog;
use App\Models\Gorder;
use App\Models\OrderServe;
use App\Models\Sorder;
use App\Models\Users;
use App\Service\MqttService;
use App\Service\TemplateService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoController extends Controller
{
    //定时任务
    public function auto()
    {
        //关闭机器

        //获取正在运行的机器，看是否到结束时间
        Log::info('++++++++++++++++++++定时任务+++++++++++++++++++'.date('Y-m-d H:i:s'));
        try {
            DB::beginTransaction();
            $time = time();
            $device_log = DeviceLog::where('open_type', 1)->where('close_time', '<', $time)->with('device')->get();
            foreach ($device_log as $value) {
                $device = $value->device;
                if ($device) {
                    //关闭机器
                    $this->closeDevice($device->device_sn);
                }
                $value->content = $value->content . "，于" . date('Y-m-d H:i:s') . "关闭[" . $device->device_name . "]设备，设备编号：[" . $device->device_sn . "]";
                $value->open_type = 0;
                $value->save();
            }

            //关闭超时未支付订单
            $now = time() - config('order_time',5)*60*60;
            Gorder::where('order_status', 0)->where('pay_status', 0)->where('submit_time', '<', $now)->update([
                'order_status' => 6,
                'cancel_remark' => '支付超时'
            ]);

            //关闭美容师未接单
            $sorder = Sorder::where('order_status',1)->where('pay_status',1)->where('serve_time','<',$time)->get();
            foreach($sorder as $val){
                //给用户退款
                $payModel = new WechatPayController($val->store_id);
                $transaction_id = $val->pay_sn;
                $refund_no = $val->order_sn;
                $total_fee = $val->total_price * 100;
                $refund_desc = '美容师尚未接单';
//                $refundNotifyUrl = url('api/v1/refund/level');
                $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
                $val->order_status = 7;
                $val->pay_status = 3;
                $result = $val->save();

                if ($result) {
                    $template = new TemplateService($val->store_id);
                    $serve = OrderServe::where('order_id', $val->id)->first();
                    $data = [
                        'first' => '用户您好，美容师超过预约时间尚未接单',
                        'keyword1' => '已拒绝',
                        'keyword2' => $serve->serve_name,
                        'keyword3' =>  date('Y-m-d H:i:s', $val->serve_time),
                        'remark' => '你可以尝试其它时段或者其它美容师继续预约，我们将恭候你的光临谢谢',
                    ];
                    $user = Users::where('id',$val->user_id)->first();
                    $template->sendTemplate($user->openid,'https://gzchenyu.cn/shopDetail',$data);
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            Log::info($exception);
            DB::rollBack();
        }



        //修改订单状态
    }


    private function closeDevice($device_sn)
    {
        $host = config("MQTT_HOST", "47.104.12.54"); //主机
        $port = config("MQTT_PORT", "4883"); //端口
        $username = config("MQTT_APPID", "6b68c250a1264025b7a546e0c66ad834");; //如果没有则为空
        $password = config("MQTT_Secret", "3a6d44d6b62c4a95afc2efad5348a989"); //如果没有则为空
        //获取设备,开启机器

        //phpMQTT 有四个参数：主机，端口，客户端id，证书。官网这里的案例没写证书，请参考phpMQTT类
        //没有证书的时候只能连接1883端口，不能连接8883端口。
        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接机器
        $mqtt->connect(true, NULL, $username, $password);
        //订阅机器
        $device_id = "6b68c250a1264025b7a546e0c66ad834/" . $device_sn;

        $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
        $mqtt->subscribe($topics, 0);
        //发送消息
            //关闭机器

        $device_control = config('global.device.device_control.close');

        $relay_control = config('global.device.relay_control.close');

        $sound = config('global.device.sound_control.close');

        //转为16进制数据
        $device_control = hex2bin(preg_replace('# #', '', $device_control));
        $relay_control = hex2bin(preg_replace('# #', '', $relay_control));
        $soundHEX = hex2bin(preg_replace('# #', '', $sound));

        $mqtt->publish($device_id, $device_control, 0);

        $mqtt->publish($device_id, $relay_control, 0);

        $mqtt->publish($device_id, $soundHEX, 0);
        //关闭连接
        $mqtt->close();

    }

}
