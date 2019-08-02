<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GoodsList;
use App\Http\Resources\GorderDetail;
use App\Http\Resources\GorderList;
use App\Http\Resources\OrderGoodsList;
use App\Models\Goods;
use App\Models\GoodsComment;
use App\Models\Gorder;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\PayLog;
use App\Models\Store;
use App\Models\Users;
use App\Service\ExpressService;
use App\Service\UploadService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends CommonController
{
    //我的订单
    public function orderMy(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $type = $request->input('type');
        $orderModel = new Gorder();
        //待支付
        if ($type == 1) {
            $orderModel = $orderModel->where('order_status', 0);
        }
        //待发货
        if ($type == 2) {
            $orderModel = $orderModel->where('order_status', 1);
        }
        //待收货
        if ($type == 3) {
            $orderModel = $orderModel->whereIn('order_status', [2, 3]);
        }
        //带评论
        if ($type == 4) {
            $orderModel = $orderModel->whereIn('order_status', [4, 5]);
        }
        $order = $orderModel->where('user_id', $user_id)->where('user_delete', 0)->with('order_goods')->orderBy('submit_time', 'desc')->get();

        $list = [];
        if ($order) {
            $list = GorderList::collection($order);
        }
        return jsonSuccess($list);
    }

    //订单详情
    public function orderDetail(Request $request)
    {
        //        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');

        if (!$order_id) {
            return jsonError('参数获取失败');
        }
        $where['user_id'] = $user_id;
        $where['id'] = $order_id;
        $where['user_delete'] = 0;
        $orderModel = new Gorder();
        $order = $orderModel->getGoodsDetail($where);
        if (!$order) {
            return jsonError('订单获取失败');
        }
        $detail = GorderDetail::make($order);
        $storeModel = new Store();
        //快递查询
        $express[] = [
            'express_time' => date('Y-m-d H:i:s'),
            'express_info' => '暂无快递信息'
        ];
        if ($order->express && $order->express_sn) {
            $result = new ExpressService($order->order_sn, $order->express, $order->express_sn);
            $res = $result->result;
//            dd($result);
            $success = data_get($res, 'Success');
            if ($success) {
                $traces = data_get($res, 'Traces');
                foreach ($traces as $val) {
                    $express[] = [
                        'express_time' => data_get($val, 'AcceptTime'),
                        'express_info' => data_get($val, 'AcceptStation')
                    ];
                }
            }
        }
        $remaining_time = ($order->submit_time + config('order_time',5)*60*60) - time();
        $data = [
            'order' => $detail,
            'express' => end($express),
            'store' => $storeModel->getDetail(['id' => $order->store_id]),
            'hour' => (int)($remaining_time/3600),
            'minute' => date('i', $remaining_time),
            'remaining_time' => $remaining_time
        ];


        return jsonSuccess($data);
    }

    //取消订单
    public function orderCancel(Request $request)
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
            $orderModel = new Gorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 0 && $order->pay_status == 0) {
                    //修改订单状态 6为已取消
                    $arr = [
                        'order_status' => 6
                    ];
                    $result = $orderModel->updateOrder($where, $arr);
                    if ($result) {
                        return jsonMsg('取消订单成功');
                    }
                    return jsonError('取消订单失败');
                }
                return jsonError('该订单不能取消');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');
    }

    //支付订单
    public function orderPay(Request $request)
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
            $orderModel = new Gorder();
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
                    $url = url('api/v1/order/notify');

                    $payController = new WechatPayController($user->store_id);
                    $body = "魅资莱商品购买";
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

    //确认收货or取货
    public function orderConfirm(Request $request)
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
            $orderModel = new Gorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 2 || $order->order_status == 3 && $order->pay_status == 1) {
                    //修改订单状态 6为已取消
                    $arr = [
                        'order_status' => 4
                    ];
                    $result = $orderModel->updateOrder($where, $arr);
                    if ($result) {
                        return jsonMsg('收货成功');
                    }
                    return jsonError('收货失败');
                }
                return jsonError('订单不能确认收货');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');
    }

    //订单评价
    public function orderComment(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $order_id = $request->input('order_id');
        $goods_id = $request->input('goods_id');
        $content = $request->input('content');
        $star = $request->input('star');
        if (!$goods_id) {
            return jsonError('选择评论的商品');
        }
        if (!$content) {
            return jsonError('请填写评论内容');
        }
        if (!$star) {
            return jsonError('请选择评分');
        }

        $images = $request->file('images');
        $image_arr = [];
        if ($images) {
            $uploads = new UploadService();
            foreach ($images as $val) {

                $image_arr[] = $uploads->upload($val);
            }
        }


        if ($star >= 4) {
            $comment_type = 1;
        } elseif ($star >= 2) {
            $comment_type = 2;
        } else {
            $comment_type = 3;
        }
        if ($order_id) {
            $where = [
                'user_id' => $user_id,
                'id' => $order_id
            ];
            $orderModel = new Gorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 4 && $order->pay_status == 1) {

                    $commentModel = new GoodsComment();
                    $arr = [
                        'content' => $content,
                        'star' => $star,
                        'user_id' => $user_id,
                        'goods_id' => $goods_id,
                        'comment_time' => time(),
                        'images' => serialize($image_arr),
                        'comment_type' => $comment_type
                    ];
                    //修改订单状态 4为已完成
                    $order->order_status = 5;

                    $order->save();

                    $result = $commentModel->addComment($arr);
                    //计算评分
                    $goods = Goods::where('id', $goods_id)->first();
                    $goods->comment_num = $goods->comment_num + 1;
                    $star = GoodsComment::where('goods_id', $goods_id)->sum('star');
                    $count = GoodsComment::where('goods_id', $goods_id)->count();
                    $star_total = $star / $count;
                    $grade = $star_total * 20;
                    $goods->grade = (int)$grade;
                    $goods->save();

                    if ($result) {
                        return jsonMsg('评论成功');
                    }
                    return jsonError('评论失败');
                }
                return jsonError('该订单不能评价');
            }
            return jsonError('订单获取失败');
        }
        return jsonError('参数错误');
    }

    //订单删除
    public function orderDelete(Request $request)
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
            $orderModel = new Gorder();
            $order = $orderModel->getDetail($where);
            if ($order) {
                if ($order->order_status == 4 || $order->order_status == 5 && $order->pay_status == 1) {
                    //修改订单状态 6为已取消
                    $arr = [
//                        'order_status' => 6,
                        'user_delete' => 1,
                    ];
                    $result = $orderModel->updateOrder($where, $arr);
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

    //支付订单回调
    public function orderNotify()
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
                    $order = Gorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->lockForUpdate()->first();
                    if ($order) {
                        $order->order_status = 7;
                        $order->pay_status = 1;
                        $order->pay_sn = $data['transaction_id'];
                        $order->pay_time = time();
                        $order->save();

                        $user = Users::where('id', $order->user_id)->first();

                        $goods = OrderGoods::where('order_id', $order->id)->get();

                        $names = '';
                        Log::info($goods);
                        foreach ($goods as $val) {
                            $names .= '[' . $val->goods_name . '] ';
                            Goods::where('id', $val->goods_id)->decrement('inventory', $val->goods_num);
                        }

                        $pay_log = new PayLog();
                        $pay_log->user_id = $order->user_id;
                        $pay_log->nickname = $user->nickname;
                        $pay_log->order_id = $order->id;
                        $pay_log->content = $user->nickname . "于" . date('Y-m-d H:i:s') . "购买了" . $names;
                        $pay_log->event_type = 'goods';
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
