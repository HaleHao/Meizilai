<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GoodsCommentList;
use App\Http\Resources\GoodsDetail;
use App\Http\Resources\GoodsList;
use App\Models\Address;
use App\Models\Category;
use App\Models\Goods;
use App\Models\GoodsComment;
use App\Models\Gorder;
use App\Models\OrderGoods;
use App\Models\Store;
use App\Models\StoreInfo;
use App\Models\UserAddress;
use App\Models\Users;
use App\Service\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GoodsController extends CommonController
{

    /**
     * 分类列表
     */
    public function categoryList()
    {
        $category = new Category();
        $list = $category->getList(['is_show' => 1]);
        return jsonSuccess($list);
    }

    /**
     * 商品列表
     */
    public function goodsList(Request $request)
    {
        $category_id = $request->input('category_id');
        $goods = new Goods();
        $where = [];
        $where['is_put'] = 1;
//        $where['goods_type'] = 0;
        if ($category_id){
            $where['category_id'] = $category_id;
        }
        $result = $goods->getList($where);
        $list = [];
        if ($result){
            $list = GoodsList::collection($result);
        }
        return jsonSuccess($list);
    }

    /**
     * 商品详情
     */
    public function goodsDetail(Request $request)
    {
        $goods_id = $request->input('goods_id');

        if ($goods_id){
            $result = Goods::where('id',$goods_id)->where('is_put',1)->with('images')->first();
            if ($result){
                $detail = GoodsDetail::make($result);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败',20002);
        }
        return jsonError('参数获取失败',20001);
    }

    /**
     * 商品评论
     */
    public function goodsCommentList(Request $request)
    {
        $goods_id = $request->input('goods_id');
        $comment_type = $request->input('comment_type');
        if ($goods_id){
            $goods = Goods::where('id',$goods_id)->first();
            $comment = new GoodsComment();
            $where['goods_id'] = $goods_id;
            if ($comment_type){
                $where['comment_type'] = $comment_type;
            }
            //评论列表
            $result = $comment->getList($where);
            if ($result){
                $result = GoodsCommentList::collection($result);
            }
            //star星级
            $star = GoodsComment::where('goods_id',$goods_id)->sum('star');
            $count = GoodsComment::where('goods_id',$goods_id)->count();
            $star_total = 5;
            if ($count){
                $star_total = $star/$count;
                //grade评分
    //            dd($star,$count);
            }

            $data = [
                'comment' => $result,
                'star_total' => $star_total,
                'grade_total' => $goods->grade,
                'comment_total' => $comment->where('goods_id',$goods_id)->count('id'),
                'good_num' => $comment->where('comment_type',1)->where('goods_id',$goods_id)->count('id'),
                'com_num' => $comment->where('comment_type',2)->where('goods_id',$goods_id)->count('id'),
                'bad_num' => $comment->where('comment_type',3)->where('goods_id',$goods_id)->count('id')
            ];
            return jsonSuccess($data);
        }
        return jsonError('Parameter Error');
    }

    /**
     * 购物车地址或者到店选择
     */
    public function goodsNext(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $goods_id = $request->input('goods_id');
        if (!$goods_id){
            return jsonError('参数错误');
        }
//        $goods_id = json_decode($goods_id,true);
        $num = $request->input('num');
        if (!$num){
            return jsonError('商品数量问题');
        }
        $user = Users::where('id',$user_id)->first();
        $store_info = StoreInfo::where('store_id',$user->store_id)->first();
        $goods = Goods::where('id',$goods_id)->first();

        if (!$goods){
            return jsonError('商品数据出错');
        }

        if ($num > $goods->inventory){
            return jsonError('购买数量不能大于库存');
        }

        $data['list'] = [[
            'id' => $goods_id,
            'name' => $goods->name,
            'num' => $num,
            'total_price' => $num * $goods->mall_price,
            'price' => $goods->mall_price,
            'carriage' => $goods->carriage,
            'cover_url' => url('uploads/'.$goods->cover_url)
        ]];
        $data['all_price'] = $num * $goods->mall_price;
        $data['carriage'] = $store_info->carriage;

        Cache::put($user_id,$data,15);
        return jsonSuccess();
    }

    /**
     * 订单确认页面
     */
    public function goodsConfirm(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $goods = Cache::get($user_id);
        $user = Users::where('id',$user_id)->first();
        $store = Store::where('id',$user->store_id)->first();
        $address = Address::where('user_id',$user_id)->orderBy('is_default',1)->first();
        $add = [];
        if ($address){
            $add = [
                'id' => $address->id,
                'consignee' => $address->consignee,
                'mobile' => $address->mobile,
                'province' => $address->province,
                'city' => $address->city,
                'district' => $address->district,
                'address' => $address->address,
            ];
        }
        $store_arr = [];
        if ($store){
            $store_arr = [
                'id' => $store->id,
                'name' => $store->name,
                'province' => $store->province,
                'city' => $store->city,
                'district' => $store->district,
                'address' => $store->address
            ];
        }
        $data = [
            'goods' => $goods,
            'store' => $store_arr,
            'address' => $add
        ];
        return jsonSuccess($data);
    }

    //商品提交
    public function goodsSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $goods_ids = $request->input('goods');
        $remark = $request->input('remark');
        $delivery_type = $request->input('delivery_type',2);
        if ($goods_ids){
            DB::beginTransaction();
//            try{
                $goods_ids = json_decode($goods_ids);
                $user = Users::where('id',$user_id)->first();
                //保存订单
                $order = new Gorder();

                if ($delivery_type == 1){
                    $address_id = $request->input('address_id');
                    if (!$address_id){
                        return jsonError('请选择收货地址');
                    }
                    $address = Address::where('id',$address_id)->first();
                    $order->username = $address->cosignee;
                    $order->mobile = $address->mobile;
                    $order->province = $address->province;
                    $order->city = $address->city;
                    $order->district = $address->district;
                    $order->address = $address->address;
                }

                $order->store_id = $user->store_id;
                $order->order_sn = $this->getOrderSn();
                $order->order_status = 0;
                $order->pay_status = 0;
                $order->user_id = $user_id;
//                $order->goods_num = 0;
                $order->delivery_method = $delivery_type;
//                $order->carriage = $carriage;
                $order->username = $user->username;
                $order->mobile = $user->mobile;
                $order->remark = $remark;
                $order->submit_time = time();
                $order->save();

                $total_price = 0;
                $goods_price = 0;
                foreach ($goods_ids as $key => $value){
                    //保存商品订单
                    $goods_order = new OrderGoods();

                    $goods = Goods::where('id',data_get($value,'goods_id'))->first();
                    $goods_order->goods_id = $goods->id;
                    $goods_order->order_id = $order->id;
                    $goods_order->goods_num = data_get($value,'num');
                    $goods_order->goods_price = $goods->mall_price;
                    $goods_order->goods_name = $goods->name;
                    $goods_order->goods_img = $goods->cover_url;
                    $goods_order->total_price = $goods->mall_price * data_get($value,'num');
                    $goods_order->submit_time = time();
                    $goods_order->save();
                    $total_price += $goods->mall_price * data_get($value,'num');
                    $goods_price += $goods->mall_price * data_get($value,'num');
                }


                if ($delivery_type == 1) {
                    $store_info = StoreInfo::where('store_id',$user->store_id)->first();
                    $carriage = $store_info->carriage;
                    $order->carriage = $carriage;
                    $total_price = $total_price + $carriage;
                }
                $order->total_price = $total_price;
                $order->goods_price = $goods_price;
                $order->save();

                DB::commit();
                return jsonSuccess(['order_id'=>$order->id],'订单生成成功');
//            }catch (\Exception $exception){
                DB::rollBack();
                return jsonError('订单生成失败');
//            }

        }
        return jsonError('参数错误');


    }

    //补货商品列表
    public function productList()
    {
        $goods = new Goods();
        $where = [];
        $where['is_put'] = 1;
        $where['goods_type'] = 1;
        $result = $goods->getList($where);
        $list = [];
        if ($result){
            $list = GoodsList::collection($result);
        }
        return jsonSuccess($list);

    }


    public function goodsComment(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }


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

        $goods = Goods::where('id',$goods_id)->first();
        if (!$goods){
            return jsonError('商品获取失败');
        }

        $images = $request->file('images');
        $image_arr = [];
        if ($images){
            $uploads = new UploadService();
            foreach ($images as $val){

                $image_arr[] = $uploads->upload($val);
            }
        }
        //四颗星以上为好评
        if ($star >= 4) {
            $comment_type = 1;
            //3颗星为一般
        } elseif ($star == 3) {
            $comment_type = 2;
            
        } else {
            $comment_type = 3;
        }

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

        $result = $commentModel->addComment($arr);

        $goods->comment_num = $goods->comment_num + 1;
        $star = GoodsComment::where('goods_id', $goods->id)->sum('star');
        $count = GoodsComment::where('goods_id', $goods->id)->count();
        $star_total = $star / $count;
        $grade = $star_total * 20;
        $goods->grade = (int)$grade;
        //保存评分信息
        $goods->save();

        if ($result) {
            return jsonMsg('评论成功');
        }
        return jsonError('评论失败');


    }

//    //商品支付
//    public function goodsPay(Request $request)
//    {
//        $user_id = $this->getUserId();
//        $order_id = $request->input('order_id');
//        if ($order_id){
//            $where = [
//                'user_id' => $user_id,
//                'order_id' => $order_id
//            ];
//            $orderModel = new Gorder();
//            $order = $orderModel->getDetail($where);
//            if ($order){
//                if ($order->order_status == 0 && $order->pay_status == 0){
//                    //支付订单
//                    $userModel = new Users();
//                    $user = $userModel->getInfo(['id' => $user_id]);
//                    $order_sn = data_get($order, 'order_sn');
//                    $openid = data_get($user, 'openid');
//                    $total_fee = data_get($order, 'total_price') * 100;
//                    $spbill_create_ip = $this->get_client_ip();
//
//                    //回调路径
//                    $url = url('api/v1/goods/notify');
//
//                    $payController = new WechatPayController($user->store_id);
//                    $body = "魅姿莱商品购买";
//                    $result = $payController->ordering($order_sn,$total_fee,$spbill_create_ip,$order_id,$openid,$url,$body);
//                    if ($result){
//                        return jsonSuccess($result);
//                    }
//                    return jsonError('支付失败');
//                }
//                return jsonError('该订单不能支付');
//            }
//            return jsonError('订单获取失败');
//        }
//        return jsonError('参数错误');
//    }
//
//    //支付回调
//    public function goodsNotify()
//    {
//
//        $data = file_get_contents("php://input");
////        Log::info(json_encode($data));
////        $wachatPay = new WechatPayController();
//        $data = $this->XmlToArr($data);
//        if ($data['return_code'] == 'SUCCESS') {
//            if ($data['result_code'] == 'SUCCESS') {
//                $orderCode = $data['out_trade_no'];
//                $total_fee = $data['total_fee'];
//                $total_fee = $total_fee / 100;
//                $result = Gorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->first();
//                if ($result) {
//                    DB::beginTransaction();
//                    try {
//                        $device_info = data_get($data,'device_info');
//                        Gorder::where('order_sn', $orderCode)->where('total_price', $total_fee)->update([
//                            'pay_status' => 1,
//                            'order_status' => 1,
//                            'pay_sn' => $data['transaction_id'],
//                            'pay_time' => $data['time_end'],
//                            'pay_name' => $device_info
//                        ]);
//                        //TODO 修改会员状态,分销信息保存
////                        Courses::where('courses_id', $result->courses_id)->increment('apply_num', 1);
////                        $shopOrderDeatilsLogics = new ShopOrderDeatilsLogics();
////
////                        $inputData = [
////                            'shop_name' => data_get(data_get($result, 'courses'), 'courses_name'),
////                            'shop_type' => 'courses',
////                            'transaction_type' => 1,
////                            'order_id' => $result->order_id,
////                            'shop_id' => $result->courses_id,
////                            'order_amount' => $result->total_fee,
////                            'user_id' => $result->user_id
////                        ];
////
////                        $shopOrderDeatilsLogics->save($inputData);
//
//                        $arr = [];
//                        $arr['return_code'] = 'SUCCESS';
//                        $arr['return_msg'] = 'OK';
//                        $return = $this->arrayToXml($arr);
//                        echo $return;
//                        DB::commit();
//                    } catch (\Exception $exception) {
//                        Log::info($exception);
//
//                        $arr = [];
//                        $arr['return_code'] = 'FAIL';
////                        $arr['return_msg'] = 'OK';
//                        $return = $this->arrayToXml($arr);
//                        echo $return;
//                        DB::rollBack();
//                    }
//                } else {
//                    $arr = [];
//                    $arr['return_code'] = 'FAIL';
////                        $arr['return_msg'] = 'OK';
//                    $return = $this->arrayToXml($arr);
//                    echo $return;
//                }
//            }
//        }
//    }
}
