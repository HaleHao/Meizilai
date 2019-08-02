<?php

namespace App\Http\Controllers\Api;

use App\Service\MqttService;
use App\Service\phpMQTT;
use App\Service\TimerService;
use App\Service\WechatTransfersService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ApplyController extends CommonController
{
    //
    public function demo2()
    {

        // 客户端调用，每隔 1 秒打印一下当前的时间

        // 第 1 个参数，表示每隔 1 秒执行一次
        // 第 2 个参数，表示 不开启 守护进程
        // 第 3 个参数，表示要做的功能
//        $timer = new TimerService(1, false, function () {
//            echo date("Y-m-d H:i:s\n");
//        });
//        $timer->start();

        ignore_user_abort();//关闭浏览器后，继续执行php代码
        set_time_limit(0);//程序执行时间无限制
        $sleep_time = 5;//多长时间执行一次
        $switch = 1;
        while ($switch) {
            $switch = 1;

            $msg = date("Y-m-d H:i:s") . $switch;
            Log::info($msg);
            sleep($sleep_time);//等待时间，进行下一次操作。
        }
        exit();


    }

    public function open(Request $request)
    {
        $host = "47.104.12.54"; //主机
        $port = '4883'; //端口
        $username = "6b68c250a1264025b7a546e0c66ad834"; //如果没有则为空
        $password = "3a6d44d6b62c4a95afc2efad5348a989"; //如果没有则为空
        $sendStr = 'EC 08 04 01 00 00 00 00 01 EF';
        $open = '10 04 01';
        $sound = '10 05 01 20';

//        $sendStr = '\xEC\x08\x04\x01\x00\x00\x00\x00\x01\xEF';
        $sendStrHEX = hex2bin(preg_replace('# #', '', $sendStr));
        $contentStrHEX = hex2bin(preg_replace('# #', '', $open));
        $soundStrHEX = hex2bin(preg_replace('# #', '', $sound));


        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接
//        if ($mqtt->connect(true,NULL,$username,$password)) {
//            $mqtt->subscribe(["6b68c250a1264025b7a546e0c66ad834/860298041791129"],0);
//            $mqtt->publish("6b68c250a1264025b7a546e0c66ad834/860298041791129",0xEC .' '. 0x08 .' '. 0x04 .' '. 0x01 .' '. 0x00 .' '. 0x00 .' '. 0x00 .' '. 0x00 .' '. 0x01 .' '. 0xEF, 0);
//            $mqtt->close(); //关闭
//            echo $this->strToHex(0x01);
//        }else{
//            echo "Fail or time out";
//        }

        $device_sn = $request->input('device_sn');
        if (!$device_sn){
            echo "没有获取到设备";
            exit;
        }
        if ($mqtt->connect(true, NULL, $username, $password)) { //链接不成功再重复执行监听连接

            $device_id = "6b68c250a1264025b7a546e0c66ad834/".$device_sn;
            $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
// 订阅主题为 SN69143809293670state qos为0
            $mqtt->subscribe($topics, 0);

//        for ($j = 0; $j < count($sendStrArray); $j++) {
//            $mqtt->publish("6b68c250a1264025b7a546e0c66ad834/860298041791129", chr(hexdec($sendStrArray[$j])), 0);
//        }
            $mqtt->publish($device_id, $sendStrHEX, 0);
            $mqtt->publish($device_id, $contentStrHEX, 0);
            $mqtt->publish($device_id, $soundStrHEX, 0);

//        while($mqtt->proc()){
//
//        }
//死循环监听
            $mqtt->close();
            echo 'open';

        }
    }

    public function close(Request $request)
    {
        $host = "47.104.12.54"; //主机
        $port = '4883'; //端口
        $username = "6b68c250a1264025b7a546e0c66ad834"; //如果没有则为空
        $password = "3a6d44d6b62c4a95afc2efad5348a989"; //如果没有则为空
        $sendStr = 'EC 08 04 00 00 00 00 00 01 EF';
        $close = '10 04 00';
        $sound = '10 05 02 20';
        $device_sn = $request->input('device_sn');
        if (!$device_sn){
            echo "没有获取到设备";
            exit;
        }
//        $sendStr = '\xEC\x08\x04\x01\x00\x00\x00\x00\x01\xEF';
        $sendStrHEX = hex2bin(preg_replace('# #', '', $sendStr));
        $contentHex = hex2bin(preg_replace('# #', '', $close));
        $soundHex = hex2bin(preg_replace('# #', '', $sound));
        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接
//        if ($mqtt->connect(true,NULL,$username,$password)) {
//            $mqtt->subscribe(["6b68c250a1264025b7a546e0c66ad834/860298041791129"],0);
//            $mqtt->publish("6b68c250a1264025b7a546e0c66ad834/860298041791129",0xEC .' '. 0x08 .' '. 0x04 .' '. 0x01 .' '. 0x00 .' '. 0x00 .' '. 0x00 .' '. 0x00 .' '. 0x01 .' '. 0xEF, 0);
//            $mqtt->close(); //关闭
//            echo $this->strToHex(0x01);
//        }else{
//            echo "Fail or time out";
//        }

        if ($mqtt->connect(true, NULL, $username, $password)) { //链接不成功再重复执行监听连接


            $device_id = "6b68c250a1264025b7a546e0c66ad834/".$device_sn;
            $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
// 订阅主题为 SN69143809293670state qos为0
            $mqtt->subscribe($topics, 0);


            $mqtt->publish($device_id, $sendStrHEX, 0);
            $mqtt->publish($device_id, $contentHex, 0);
            $mqtt->publish($device_id, $soundHex, 0);


            $mqtt->close();
            echo 'close';
        }
    }


    public function demo3()
    {
        $wechat = new WechatTransfersService(4);
        $wechat->publicEncrypt('');
    }


    function procmsg($topic, $msg)
    { //信息回调函数 打印信息
        echo "Msg Recieved: " . date("r") . "\n";
        echo "Topic: {$topic}\n\n";
        echo "\t$msg\n\n";
        $xxx = json_decode($msg);
//        var_dump($xxxxxx->aa);
        die;
    }


    function strToHex($str)
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++)
            $hex .= dechex(ord($str[$i]));
        $hex = strtoupper($hex);
        return $hex;
    }


}
