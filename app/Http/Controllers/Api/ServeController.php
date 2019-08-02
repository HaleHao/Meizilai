<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ServeDetail;
use App\Http\Resources\ServeList;
use App\Http\Resources\ServeOrderList;
use App\Models\OrderServe;
use App\Models\PayLog;
use App\Models\Sign;
use App\Models\Sorder;
use App\Models\Store;
use App\Models\StoreComment;
use App\Models\StoreServe;
use App\Models\Users;
use App\Service\TemplateService;
use App\Service\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ServeController extends CommonController
{

    //我的服务
    public function serveMy(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $user_id]);
//        $order_status = $request->input('order_status');
        $type = $request->input('type');
        if ($user->is_beautician == 1 && $user->user_type == 3) {
            $order = new Sorder();
            if ($type == 1) {

                $order = $order->where('order_status', 1);
            }
            if ($type == 2) {

                $order = $order->where('order_status', 2);
            }
            if ($type == 3) {

                $order = $order->whereIn('order_status', [4, 5, 6]);
            }
            if ($type == 4) {

                $order = $order->where('order_status', 3);
            }

            $order = $order->where('beautician_id', $user_id)->with(['user' => function ($query) {
                return $query->with('card');
            }])->with('orderServe')->with('beautician')->orderBy('submit_time', 'desc')->get();
            $list = [];
            if ($order) {
                $list = ServeList::collection($order);
            }
            return jsonSuccess($list);
        }
        return jsonSuccess('您不是美容师');
    }

    //服务详情
    public function serveDetail(Request $request)
    {
//        $user_id = $this->getUserId();
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $order_id = $request->input('order_id');
        if ($order_id) {
            $sorder = Sorder::where('beautician_id', $user_id)->where('id', $order_id)->first();
            if ($sorder) {
                $detail = ServeDetail::make($sorder);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数错误');
    }

    //服务星期时间选择
    public function serveWeek()
    {
        $begin_time = time();
        $end_time = strtotime('+7 days');

        for ($start = $begin_time; $start <= $end_time; $start += 24 * 3600) {
            $date[] = date('Y-m-d', $start);
        }
        $serve['weekday'] = $date;
        return jsonSuccess($serve);
    }

    //服务具体时间选择
    public function serveTime(Request $request)
    {
        $weekday = $request->input('weekday', date('Y-m-d'));
        for ($i = 0; $i < 144; $i++) {
            $e = strtotime($weekday) + (($i + 1) * 600);
            if (date("Y-m-d H:i", $e) > date('Y-m-d H:i')) {
                $arr1[] = date("H:i", $e);
            }
            if (date("Y-m-d H:i", $e) < date('Y-m-d 23:59')) {
//                dd(2);
                $arr2[] = date('H:i', $e);
            }
        }
        if ($arr1) {
            $time_arr = $arr1;
        } else {
            $time_arr = $arr2;
        }
        $serve['time'] = $time_arr;
        return jsonSuccess($serve);
    }

    //服务拒绝
    public function serveReject(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        $remark = $request->input('remark');
        if (!$remark) {
            return jsonError('请填写拒绝理由');
        }
        if ($order_id) {
            $where['beautician_id'] = $user_id;
            $where['id'] = $order_id;
            $sorderModel = new Sorder();
            $sorder = $sorderModel->getServeDetail($where);
            if ($sorder) {
                if ($sorder->order_status == 1 && $sorder->order_status == 1) {
                    //退款给用户
                    $payModel = new WechatPayController($sorder->store_id);
                    $transaction_id = $sorder->pay_sn;
                    $refund_no = $sorder->order_sn;
                    $total_fee = $sorder->total_price * 100;
                    $refund_desc = '美容师' . $remark . ',拒绝您申请的美容服务';
                    $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);


                    $sorder->order_status = 3;
                    $sorder->pay_status = 3;
                    $sorder->remark = $remark;
                    $sorder->cancel_time = time();
                    $sorder->save();
                    if ($sorder) {
                        //发送模板消息
                        $template = new TemplateService($sorder->store_id);
                        $serve = OrderServe::where('order_id', $sorder->id)->first();
                        $data = [
                            'first' => '用户您好，美容师' . $remark . ',拒绝您申请的美容服务',
                            'keyword1' => '已拒绝',
                            'keyword2' => $serve->serve_name,
                            'keyword3' =>  date('Y-m-d H:i:s', $sorder->serve_time),
                            'remark' => '你可以尝试其它时段或者其它美容师继续预约，我们将恭候你的光临谢谢',
                        ];
                        $user = Users::where('id',$sorder->user_id)->first();
                        $template->sendTemplate($user->openid,'https://gzchenyu.cn/shopDetail',$data);

                        return jsonMsg('拒绝服务成功');
                    }
                    return jsonError('拒绝服务失败');
                }
                return jsonMsg('不能拒绝该订单');
            }
            return jsonError('获取数据失败');
        }
        return jsonError('参数错误');
    }

    //服务同意
    public function serveAgree(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        if ($order_id) {
            $where['beautician_id'] = $user_id;
            $where['id'] = $order_id;
            $sorderModel = new Sorder();
            $sorder = $sorderModel->getServeDetail($where);
            if ($sorder) {
                if ($sorder->serve_time < time()) {

                    return jsonError('接单失败，超过服务时间');
                }
                if ($sorder->order_status == 1 && $sorder->order_status == 1) {
                    $sorder->order_status = 2;
                    $result = $sorder->save();
                    if ($result) {

                        //发送模板消息
                        $template = new TemplateService($sorder->store_id);
                        $serve = OrderServe::where('order_id', $sorder->id)->first();
                        $data = [
                            'first' => '用户您好，美容师同意您申请的美容服务',
                            'keyword1' => '待服务',
                            'keyword2' => $serve->serve_name,
                            'keyword3' =>  date('Y-m-d H:i:s', $sorder->serve_time),
                            'remark' => '请于指定时间到店接受服务。',
                        ];
                        $user = Users::where('id',$sorder->user_id)->first();
                        $template->sendTemplate($user->openid,'https://gzchenyu.cn/applyOrder',$data);


                        return jsonMsg('同意服务成功');
                    }
                    return jsonError('同意服务失败');
                }
                return jsonError('不能接该订单');

            }
            return jsonError('获取数据失败');
        }
        return jsonError('参数错误');
    }

    //申请订单
    public function serveOrder(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $sorderModel = new Sorder();
        $where = [
            'user_id' => $user_id,
            'user_delete' => 0,
        ];
        $sorder = $sorderModel->getOrder($where);
//        dd($sorder);
        $list = [];
        if ($sorder) {
            $list = ServeOrderList::collection($sorder);
        }
        return jsonSuccess($list);
    }

    //服务申请
    public function serveApply(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $serve_ids = $request->input('serve_ids');
        $beautician_id = $request->input('beautician_id');
//        $total_price = $request->input('total_price');
        $serve_time = $request->input('serve_time');
        //
        if (!$serve_ids) {
            return jsonError('请选择服务');
        }
        if (!$beautician_id) {
            return jsonError('请选择美容师');
        }
        if (!$serve_time) {
            return jsonError('请选择预约时间');
        }
        $serve_time = strtotime($serve_time);
        //判断美容师是否被预约
        $result = Sorder::where('serve_time', $serve_time)->whereIn('order_status', [1, 2])->where('beautician_id', $beautician_id)->first();
        if ($result) {
            return jsonError('美容师该时间段已被预约');
        }


        try {
            //保存订单
            DB::beginTransaction();

            $userModel = new Users();
            $user = $userModel->getInfo(['id' => $user_id]);
            $time = time();

            $order = new Sorder();
            $order->order_sn = $this->getOrderSn();
            $order->order_status = 0;
            $order->pay_status = 0;
            $order->beautician_id = $beautician_id;
            $order->user_id = $user_id;
            $order->mobile = $user->mobile;
            $order->apply_time = $time;
            $order->username = $user->username;
            $order->serve_time = $serve_time;
            $order->submit_time = $time;
            $order->store_id = $user->store_id;
            $order->save();

            //保存服务订单
            $total_price = 0;
            $serve_ids = json_decode($serve_ids, true);
            if (is_array($serve_ids)) {
                foreach ($serve_ids as $key => $value) {
                    $serve = StoreServe::where('id', $value)->first();
                    $serve_order[] = [
                        'order_id' => $order->id,
                        'user_id' => $user_id,
                        'beautician_id' => $beautician_id,
                        'serve_name' => $serve->name,
                        'serve_time' => $serve_time,
                        'serve_price' => $serve->price,
                        'serve_id' => $serve->id,
                        'submit_time' => time(),
                    ];
                    $total_price += $serve->price;
                }
            } else {
                $serve = StoreServe::where('id', $serve_ids)->first();
                $serve_order[] = [
                    'order_id' => $order->id,
                    'user_id' => $user_id,
                    'beautician_id' => $beautician_id,
                    'serve_name' => $serve->name,
                    'serve_time' => $serve_time,
                    'serve_price' => $serve->price,
                    'serve_id' => $serve->id,
                    'submit_time' => time(),
                ];
                $total_price = $serve->price;
            }
            $order->total_price = $total_price;
            $order->save();
            OrderServe::insert($serve_order);

            DB::commit();
            return jsonSuccess($order, '订单生成成功');
        } catch (\Exception $exception) {
            Log::info($exception);
            DB::rollBack();
            return jsonError('订单生成失败');
        }
    }

    //服务评价
    public function serveComment(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $content = $request->input('content');
        $order_id = $request->input('order_id');
        $grade = $request->input('grade');
        $images = $request->file('images');
        $image_arr = [];
        if ($images) {
            $uploads = new UploadService();
            foreach ($images as $val) {
                $image_arr[] = $uploads->upload($val);
            }
        }
        if (!$content) {
            return jsonError('请填写评论内容');
        }
        if (!$grade) {
            return jsonError('请选择评分');
        }
        if ($order_id) {
            $where = [
                'user_id' => $user_id,
                'id' => $order_id
            ];
            $orderModel = new Sorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 5 && $order->pay_status == 1) {
                    //修改订单状态 6为已取消
                    try {
                        DB::beginTransaction();
                        $commentModel = new StoreComment();

                        $beautician_id = $order->beautician_id;
                        $commentModel->content = $content;
                        $commentModel->grade = $grade;
                        $commentModel->beautician_id = $beautician_id;
                        $commentModel->user_id = $user_id;
                        $commentModel->store_id = $order->store_id;
                        $commentModel->comment_time = time();
                        $commentModel->images = serialize($image_arr);
                        $commentModel->save();
                        // 计算评论评分
                        $total_num = StoreComment::where('beautician_id', $beautician_id)->count();

                        $goods_num = StoreComment::where('beautician_id', $beautician_id)->where('grade', 1)->count();

                        $grade = (int)round(($goods_num / $total_num) * 100);
//                        $userModel = new Users();
//                        $userModel->updateInfo(['id' => $beautician_id], ['grade' => $grade]);
                        $user = Users::where('id', $beautician_id)->first();
                        $user->grade = $grade;
                        $user->save();
                        $order->order_status = 6;
                        $order->save();
                        DB::commit();
                        return jsonMsg('评论成功');
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        return jsonError('评论失败');
                    }
                }
                return jsonError('该订单不能评价');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');

    }

    //删除服务
    public function serveDelete(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }


        $order_id = $request->input('order_id');
        if ($order_id) {
            $where = [
                'user_id' => $user_id,
                'id' => $order_id
            ];
            $orderModel = new Sorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 0 || $order->order_status == 3 || $order->order_status == 6 || $order->order_status == 7) {
                    $order->user_delete = 1;
                    $result = $order->save();
                    if ($result) {
                        return jsonMsg('订单删除成功');
                    }
                    return jsonError('订单删除失败');
                }
                return jsonError('该订单不能删除');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');

    }

    //确认服务订单
    public function serveConfirm(Request $request)
    {

        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $user_id]);
        if (!$user) {
            return jsonLoginError();
        }

        $serve_ids = $request->input('serve_ids');
        if (!$serve_ids) {
            return jsonError('请选择服务项目');
        }
        $serve_ids = json_decode($serve_ids);
        $serveModel = new StoreServe();
        $total_price = 0;
        $serve_arr = [];
        if (is_array($serve_ids)) {
            foreach ($serve_ids as $key => $value) {
                $serve = $serveModel->getDetail(['id' => $value]);
                $total_price += $serve->price;
                $serve_arr[$key]['id'] = $serve->id;
                $serve_arr[$key]['serve_name'] = $serve->name;
                $serve_arr[$key]['price'] = $serve->price;
            }
        } else {
            $serve = $serveModel->getDetail(['id' => $serve_ids]);
            $serve_arr[0]['id'] = $serve->id;
            $serve_arr[0]['serve_name'] = $serve->name;
            $serve_arr[0]['price'] = $serve->price;
            $total_price = $serve->price;
        }
        $date = date('Y-m-d');
        $sign = Sign::where('store_id', $user->store_id)->where('sign_date', $date)->get();
        $arr = [];
        foreach ($sign as $value) {
            $beautician = $userModel->where('id', $value->user_id)->where('store_id', $user->store_id)
                ->where('is_beautician', 1)->where('user_type', 3)->select(['id', 'username', 'avatar', 'grade'])->first();
            if ($beautician) {
                $arr[] = $beautician;
            }
        }

        $mobile = $user->mobile;
        $username = $user->username;
        $data = [
            'serve' => $serve_arr,
            'username' => $username,
            'mobile' => $mobile,
            'total_price' => $total_price,
            'beautician' => $arr
        ];
        Cache::put($user_id, $data, 15);

        return jsonSuccess();
    }

    //下单页面
    public function serveNext(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $serve = Cache::get($user_id);
//        $begin_time = time();
//        $end_time = strtotime('+7 days');
//
//        for ($start = $begin_time; $start <= $end_time; $start += 24 * 3600) {
//            $date[] = date('Y-m-d', $start);
//        }
//        $time_arr = [];
//        foreach ($date as $key => $value) {
//            for ($i = 0; $i < 48; $i++) {
//                $e = strtotime($value) + (($i + 1) * 1800);
//                if (date("Y-m-d H:i", $e) > date('Y-m-d H:i')) {
//                    $arr1[] = ['time' => date("H:i", $e)];
//                }
//                if (date("Y-m-d H:i", $e) < date('Y-m-d 23:59')) {
//                    $arr2[] = ['time' => date('H:i', $e)];
//                }
//            }
//            if ($value == date('Y-m-d')) {
//
//                $time_arr[$key]['week_time'] = date('m-d', strtotime($value));
//                $time_arr[$key]['time'] = $arr1;
//            } else {
//                $time_arr[$key]['week_time'] = date('m-d', strtotime($value));
//                $time_arr[$key]['time'] = $arr2;
//            }
//        }
//
//        $serve['serve_time'] = $time_arr;
        if ($serve) {
            return jsonSuccess($serve);
        }
        return jsonError('服务获取失败');
    }

    //取消服务订单
    public function serveCancel(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');

        $sorder = Sorder::where('id', $order_id)->where('user_id', $user_id)->lockForUpdate()->first();

        if ($sorder) {
            if ($sorder->serve_time >= time()) {
                return jsonError('该订单超过服务时间不能取消');
            }
            if ($sorder->order_status == 1 && $sorder->pay_status == 1) {

                $payModel = new WechatPayController($sorder->store_id);
                $transaction_id = $sorder->pay_sn;
                $refund_no = $sorder->order_sn;
                $total_fee = $sorder->total_price * 100;
                $refund_desc = '您取消了预约';
//                $refundNotifyUrl = url('api/v1/refund/level');
                $result = $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
                if ($result) {
                    $sorder->order_status = 7;
                    $sorder->pay_status = 3;
                    $sorder->save();
                    return jsonMsg('订单取消成功');
                }
                return jsonError('退款失败,订单取消失败');
            }
            return jsonError('该订单不能取消');
        }
        return jsonError('参数获取失败');
    }

    //服务支付
    public function servePay(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        if ($order_id) {
            $where = [
                'user_id' => $user_id,
                'id' => $order_id
            ];
            $orderModel = new Sorder();
            $order = $orderModel->getDetail($where);
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
                    $url = url('api/v1/serve/notify');

                    $payController = new WechatPayController($user->store_id);
                    $body = "魅资莱服务申请";
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

    //支付回调
    public function serveNotify()
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
                DB::beginTransaction();
                try {
                    $order = Sorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->lockForUpdate()->first();
                    if ($order) {
                        $order->order_status = 1;
                        $order->pay_status = 1;
                        $order->pay_sn = $data['transaction_id'];
                        $order->pay_time = time();
                        $order->save();

                        $user = Users::where('id', $order->user_id)->first();

                        $goods = OrderServe::where('order_id', $order->id)->get();

                        $names = '';
                        foreach ($goods as $val) {
                            $names .= '[' . $val->name . '] ';
                        }

                        $pay_log = new PayLog();
                        $pay_log->user_id = $order->user_id;
                        $pay_log->nickname = $user->nickname;
                        $pay_log->order_id = $order->id;
                        $pay_log->content = $user->nickname . "于" . date('Y-m-d H:i:s') . "申请了" . $names;
                        $pay_log->event_type = 'card';
                        $pay_log->happen_time = time();
                        $pay_log->save();

                        //给关注的美容师发送预约消息
                        $beautician = Users::where('id', $order->beautician_id)->first();
                        $template = new TemplateService($order->store_id);
                        $serve = OrderServe::where('order_id', $order->id)->first();
                        $data = [
                            'first' => '美容师您好，您有新的预约订单：',
                            'keyword1' => '待确认',
                            'keyword2' => $serve->serve_name,
                            'keyword3' =>  date('Y-m-d H:i:s', $order->serve_time),
                            'remark' => '请留意预约时间，并及时处理预约订单！',
                        ];
                        $template->sendTemplate($beautician->openid, 'https://gzchenyu.cn/service', $data);

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
