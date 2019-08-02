<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartList;
use App\Models\Address;
use App\Models\Goods;
use App\Models\Gorder;
use App\Models\OrderGoods;
use App\Models\ShopCart;
use App\Models\Store;
use App\Models\StoreInfo;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CartController extends CommonController
{
    protected $cartModel;

    public function __construct()
    {
        $this->cartModel = new ShopCart();
    }

    /**
     * 购物车列表
     */
    public function cartList(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $where = [
            'user_id' => $user_id
        ];
        $cart = $this->cartModel->getCart($where);
        $list = [];
        //判断购物车是否有数据
        if ($cart) {
            //将删除的商品进行删除
            foreach ($cart as $value) {
                $goods = Goods::where('id', $value->goods_id)->first();
                if ($goods) {
                    $list[] = [
                        'cart_id' => $value->id,
                        'goods_id' => $value->goods->id,
                        'goods_name' => $value->goods->name,
                        'num' => $value->total_num,
                        'price' => $value->goods->mall_price,
                        'cover_url' => url('uploads/' . $value->goods->cover_url)
                    ];
                }else{
                    $this->cartModel->where('id',$value->id)->delete();
                }
            }
        }
        return jsonSuccess($list);
    }

    /**
     * 购物车删除
     */
    public function cartDelete(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $cart_ids = $request->input('cart_ids');
        $ids = json_decode($cart_ids);
        if ($ids) {
            try {
                foreach ($ids as $id) {
                    $this->cartModel->where('user_id', $user_id)->where('id', $id)->delete();
                }
                $where = [
                    'user_id' => $user_id
                ];
                $cart = $this->cartModel->getCart($where);
                $list = [];
                if ($cart) {
                    $list = CartList::collection($cart);
                }
                return jsonSuccess($list, '删除成功');
            } catch (\Exception $exception) {
                return jsonError('删除失败');
            }
        }
        return jsonError('请选择删除的商品');
    }

    /**
     * 添加购物车
     */
    public function cartAdd(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        //获取商品ID
        $goods_id = $request->input('goods_id');
        //获取商品数量
        $num = $request->input('num');

        $goods = Goods::where('id', $goods_id)->first();
        if ($goods_id && $goods) {
            if ($num) {
                $where = [
                    'user_id' => $user_id,
                    'goods_id' => $goods_id
                ];

                $cart = $this->cartModel->firstCart($where);
                //查询是否有相同的商品，如果有就相加
                if ($cart) {
                    $total_num = $cart->total_num + $num;
                    $cart['total_num'] = $total_num;
                    $cart['total_price'] = $total_num * $goods->mall_price;
                    $result = $cart->save();
                } else {
                    $arr = [
                        'goods_id' => $goods_id,
                        'user_id' => $user_id,
                        'total_num' => $num,
                        'total_price' => $goods->mall_price * $num,
                        'add_time' => time()
                    ];
                    $result = $this->cartModel->addCart($arr);
                }
                if ($result) {
                    return jsonMsg('添加成功');
                }
                return jsonError('添加失败');
            }
            return jsonError('请选择正确的数量');
        }
        return jsonError('选择添加到购物车的商品');
    }

    /**
     * 购物车数量更新
     */
    public function cartUpdate(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $cart_id = $request->input('cart_id');
        $num = $request->input('num');
        if (!$cart_id) {
            return jsonError('参数获取失败');
        }
        if (!$num) {
            return jsonError('数量不正确');
        }
        try {
            $cart = ShopCart::where('id', $cart_id)->first();
            if (!$cart){
                return jsonError('购物车获取失败');
            }
            $goods = Goods::where('id', $cart->goods_id)->first();
            if (!$goods){
                return jsonError('商品获取失败');
            }
            if ($num > $goods->inventory){
                return jsonError('购买数量不能大于库存');
            }
            $cart->total_num = $num;
            $cart->total_price = $num * $goods->mall_price;
            $cart->save();
            $where = [
                'user_id' => $user_id
            ];
            $cart_list = $this->cartModel->getCart($where);
            $list = [];
            if ($cart_list) {
                $list = CartList::collection($cart_list);
            }
            return jsonSuccess($list, '修改成功');
        } catch (\Exception $exception) {
            return jsonError('修改失败');
        }

    }

    /**
     * 购物车next
     */
    public function cartNext(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $user = Users::where('id', $user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $cart_ids = $request->input('cart_ids');

        if ($cart_ids) {

            try {
                $store_info = StoreInfo::where('store_id', $user->store_id)->first();
                $cart_ids = json_decode($cart_ids);
                $total_price = 0;
                foreach ($cart_ids as $id) {
                    $cart = ShopCart::where('id', $id)->first();
                    $goods = Goods::where('id', $cart->goods_id)->first();
                    $arr[] = [
                        'id' => $goods->id,
                        'name' => $goods->name,
                        'num' => $cart->total_num,
                        'total_price' => $cart->total_num * $goods->mall_price,
                        'price' => $goods->mall_price,
                        'cover_url' => url('uploads/' . $goods->cover_url)
                    ];
//                    $carriage = $goods->carriage;
                    $total_price += $cart->total_num * $goods->mall_price;
                }
                $data['list'] = $arr;
                //保存运费
                $data['all_price'] = $total_price;

                $data['carriage'] = $store_info->carriage;
                Cache::put($user_id, $data, 15);
                return jsonMsg('数据保存成功');
            } catch (\Exception $exception) {
                return jsonError('数据保存失败');
            }
        }
        return jsonError('参数获取失败');
    }

    /**
     * 购物车确认页面
     */
    public function cartConfirm(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        //获取缓存数据
        $goods = Cache::get($user_id);
        $user = Users::where('id', $user_id)->first();
        $store = Store::where('id', $user->store_id)->first();
        $address = Address::where('user_id', $user_id)->orderBy('is_default', 1)->first();
        $data = [
            'goods' => $goods,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'province' => $store->province,
                'city' => $store->city,
                'district' => $store->district,
                'address' => $store->address
            ],
            'address' => [
                'id' => $address->id,
                'consignee' => $address->consignee,
                'mobile' => $address->mobile,
                'province' => $address->province,
                'city' => $address->city,
                'district' => $address->district,
                'address' => $address->address,
            ]
        ];
        return jsonSuccess($data);
    }

    /**
     * 购物车订单提交
     */
    public function cartSubmit(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $goods_id = $request->input('goods');
        $remark = $request->input('remark');
        $num = $request->input('num');
        $delivery_type = $request->input('delivery_type');
        if ($goods_id) {
            DB::beginTransaction();
            try {
                $goods = Goods::where('id', $goods_id)->first();
                $user = Users::where('id', $user_id)->first();
                //保存订单
                $order = new Gorder();

                if ($delivery_type == 1) {
                    $address_id = $request->input('address_id');
                    if (!$address_id) {
                        return jsonError('请选择收货地址');
                    }
                    $address = Address::where('id', $address_id)->first();
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
//                $order->goods_num = $num;
                $order->delivery_method = $delivery_type;
//                $order->carriage = $carriage;
                $order->username = $user->username;
                $order->mobile = $user->mobile;
                $order->remark = $remark;
                $order->submit_time = time();
                $order->save();
                //保存商品订单

                $goods_order = new OrderGoods();
                $goods_order->goods_id = $goods->id;
                $goods_order->order_id = $order->id;
                $goods_order->goods_num = $num;
                $goods_order->goods_price = $goods->mall_price;
                $goods_order->goods_name = $goods->name;
                $goods_order->goods_img = $goods->cover_url;
                $goods_order->total_price = $goods->mall_price * $num;
                $goods_order->submit_time = time();

                $total_price = $goods->mall_price * $num;

                $carriage = $goods->carriage;

                $order->goods_price = $total_price;
                if ($delivery_type == 1) {
                    $order->carriage = $carriage;
                    $order->total_price = $carriage + $total_price;
                }

                $order->save();
                $goods_order->save();

                DB::commit();
                return jsonSuccess(['order_id' => $order->id], '订单生成成功');
            } catch (\Exception $exception) {
                DB::rollBack();
                return jsonError('订单生成失败');
            }

        }
        return jsonError('参数错误');
    }
}
