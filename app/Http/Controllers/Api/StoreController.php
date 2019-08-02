<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BeauticianCommentList;
use App\Http\Resources\BeauticianDetail;
use App\Http\Resources\BeauticianList;
use App\Http\Resources\GoodsCommentList;
use App\Http\Resources\LevelOrderList;
use App\Http\Resources\OrderGoodsList;
use App\Http\Resources\ServeList;
use App\Http\Resources\StoreCommentList;
use App\Http\Resources\StoreDetail;
use App\Http\Resources\StoreGorderList;
use App\Http\Resources\StoreList;
use App\Models\EarningsLog;
use App\Models\Goods;
use App\Models\Gorder;
use App\Models\Lorder;
use App\Models\Sign;
use App\Models\Sorder;
use App\Models\Store;
use App\Models\StoreBeautician;
use App\Models\StoreComment;
use App\Models\StoreServe;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpParser\Comment;
use Symfony\Component\VarDumper\Cloner\Data;

class StoreController extends CommonController
{

    /**
     * 商店列表
     */
    public function storeList(Request $request)
    {

        $user_lng = $request->input('longitude', "114.040833");
        $user_lat = $request->input('latitude', "22.617972");
        $keyword = $request->input('keyword');


        $storeModel = new Store();
        $storeModel = $storeModel->where('is_lock', 0);

        $city = $request->input('city');
        if (!$city) {
            $key = config('MAP_KEY', 'KKXBZ-ZDEWU-VQIV5-4DFHT-A447H-LJFDY');
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=" . $user_lat . "," . $user_lng . "&key=" . $key . "&get_poi=1";
            $html = file_get_contents($url);
            $result = json_decode($html);
            $city = data_get(data_get(data_get($result, 'result'), 'address_component'), 'city');
        }
        if ($city) {
            $city_str = mb_substr($city, 0, 2, 'UTF-8');
//            $where['city'] = ['like' => '%'.$city.'%'];
            $storeModel = $storeModel->where('city', 'like', '%' . $city_str . '%');
        }
        if ($keyword) {
            $storeModel = $storeModel->where('name', 'like', '%' . $keyword . '%');
        }


        $store = $storeModel->get();
//                dd($store);
        $list = [];
        if ($store) {
            foreach ($store as $key => $value) {
                $distance = $this->getDistance($user_lng, $user_lat, $value->lng, $value->lat);
                $distance = round((int)$distance / 1000, 1);
                $store[$key]['distance'] = $distance;
            }
            $list = StoreList::collection($store);
        }
        $data = [
            'list' => $list,
            'city' => $city
        ];
        return jsonSuccess($data);
    }


    public function getCity($url)
    {
        // 初始化一个 cURL 对象
        $curl = curl_init();
        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置header 响应头是否输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        // 1如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
        // 运行cURL，请求网页
        $data = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);

        return $data;
    }

    /**
     * 商店详情，需要用户通过扫码进入
     */
    public function storeDetail(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $store_id = $request->input('store_id');
        $user = Users::where('id', $user_id)->where('store_id', $store_id)->first();
        if (!$user) {
            return jsonLoginError();
        }
        if ($store_id) {
            $where = [
                'id' => $store_id,
                'is_lock' => 0
            ];
            $storeModel = new Store();
            $store = $storeModel->getDetail($where);
            if ($store) {
                $detail = StoreDetail::make($store);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 店铺服务列表
     */
    public function serveList(Request $request)
    {
        $store_id = $request->input('store_id');
        if ($store_id) {
            $where = [
                'store_id' => $store_id,
            ];
            $serveModel = new StoreServe();
            $serve = $serveModel->getList($where);
            return jsonSuccess($serve);
        }
        return jsonError('参数获取失败');
    }

    /**
     * 获取美容师列表
     */
    public function beauticianList(Request $request)
    {
        $store_id = $request->input('store_id');
        if ($store_id) {
            //
            $beautician = Users::where('store_id', $store_id)->where('user_type', 3)->where('is_beautician', 1)
                ->select('id', 'avatar', 'username', 'nickname', 'grade', 'serve_num')->get();
            $list = [];
            if ($beautician) {
                $list = BeauticianList::collection($beautician);
            }
            return jsonSuccess($list);
        }
        return jsonError('参数获取失败');
    }

    /**
     * 获取美容师详情
     */
    public function beauticianDetail(Request $request)
    {
        $beautician_id = $request->input('beautician_id');
        if ($beautician_id) {
            $beautician = Users::where('id', $beautician_id)->with(['comment' => function ($query) {
                $query->with('user');
            }])->first();
            if ($beautician) {
                $detail = BeauticianDetail::make($beautician);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 店铺评论列表
     */
    public function commentList(Request $request)
    {
        $store_id = $request->input('store_id');
        $grade = $request->input('grade');
        $beautician_id = $request->input('beautician_id');
        if ($store_id) {
            $where ['store_id'] = $store_id;
            if ($grade) {
                $where['grade'] = $grade;
            }
            if ($beautician_id) {
                $where['beautician_id'] = $beautician_id;
            }
            $commentModel = new StoreComment();
            $comment = $commentModel->getList($where);
            $list = [];
            if ($comment) {
                $list = StoreCommentList::collection($comment);
            }
            $data = [
                'total_num' => $commentModel->getNum(),
                'good_num' => $commentModel->getNum(['grade' => 1]),
                'com_num' => $commentModel->getNum(['grade' => 2]),
                'bad_num' => $commentModel->getNum(['grade' => 3]),
                'comment' => $list
            ];
            return jsonSuccess($data);
        }
        return jsonError('参数获取失败');
    }

    public function beauticianCommentList(Request $request)
    {
        $beautician_id = $request->input('beautician_id');
        $comment_type = $request->input('comment_type');
        if ($beautician_id) {
            $user = Users::where('id', $beautician_id)->first();
            $comment = new StoreComment();
            $where['beautician_id'] = $beautician_id;
            if ($comment_type) {
                $where['grade'] = $comment_type;
            }
            //评论列表
            $result = $comment->getList($where);
            if ($result) {
                $result = BeauticianCommentList::collection($result);
            }
            //star星级
            $grade = $user->grade;
            if ($grade >= 0) {
                $star_total = 1;
            }
            if ($grade >= 20) {
                $star_total = 2;
            }
            if ($grade >= 40) {
                $star_total = 3;
            }
            if ($grade >= 60) {
                $star_total = 4;
            }
            if ($grade >= 80) {
                $star_total = 5;
            }

            $data = [
                'comment' => $result,
                'star_total' => $star_total,
                'grade_total' => $user->grade,
                'comment_total' => $comment->where('beautician_id', $beautician_id)->count('id'),
                'good_num' => $comment->where('grade', 1)->where('beautician_id', $beautician_id)->count('id'),
                'com_num' => $comment->where('grade', 2)->where('beautician_id', $beautician_id)->count('id'),
                'bad_num' => $comment->where('grade', 3)->where('beautician_id', $beautician_id)->count('id')
            ];
            return jsonSuccess($data);
        }
        return jsonError('Parameter Error');
    }

    /**
     * 直接授权
     */
    public function wechatLogin(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }
        $store_id = $request->input('store_id');
        $user = Users::where('id', $user_id)->first();
        if ($user->store_id != $store_id) {
            return jsonError("用户所在店铺不同");
        }
        if ($user->is_storekeeper != 1 || $user->user_type != 4) {
            return jsonError('您不是该店店主');
        }
        $access_token = $this->getToken();
        $result = Cache::add($access_token, $user_id, 2 * 60);
        if ($result) {
            return jsonSuccess(['store_token' => $access_token], '登录成功');
        }
        return jsonError('登录失败');
    }

    /**
     * 店铺后台
     */
    public function adminLogin(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id) {
            return jsonLoginError();
        }

        $store_id = $request->input('store_id');

        $mobile = $request->input('mobile');

        if (!$mobile) {
            return jsonError("请输入手机号码");
        }
        $password = $request->input('password');
        if (!$password) {
            return jsonError("请输入密码");
        }

        $user = Users::where('id', $user_id)->first();
        if (encrypt($password) != $user->password && $user->mobile != $mobile) {
            return jsonError("账号或者密码不正确");

        }
        if ($user->store_id != $store_id) {
            return jsonError("用户所在店铺不同");
        }
        if ($user->is_storekeeper != 1 || $user->user_type != 4) {
            return jsonError('您不是该店店主');
        }

        $access_token = $this->getToken();
        $result = Cache::add($access_token, $user_id, 2 * 60);
        if ($result) {
            return jsonSuccess(['store_token' => $access_token], '登录成功');
        }
        return jsonError('登录失败');
    }

    /**
     * 店铺后台首页
     */
    public function admin(Request $request)
    {
        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }
        //
        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if ($storekeeper->is_storekeeper != 1 && $storekeeper->user_type != 4) {
            return jsonError('您不是该店店主');
        }
        $store_id = $storekeeper->store_id;
        $store = Store::where('id', $storekeeper->store_id)->first();
        $yesterday_start_time = strtotime(date("Y-m-d", strtotime("-1 day")));   //昨天开始时间
        $yesterday_end_time = $yesterday_start_time + 24 * 60 * 60 - 1;  //昨天结束时间
        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time = strtotime(date('Y-m-d 23:59:59'));
        //昨日收益
        $yesterday_earnings = EarningsLog::where('store_id', $store_id)->whereBetween('add_time', [$yesterday_start_time, $yesterday_end_time])->sum('earnings_amount');
        //今日收益
        $today_earnings = EarningsLog::where('store_id', $store_id)->whereBetween('add_time', [$start_time, $end_time])->sum('earnings_amount');
        //累计收益
        $total_earnings = EarningsLog::where('store_id', $store_id)->sum('earnings_amount');
        //今日商品订单数
        $today_goods = Gorder::where('store_id', $store_id)->where('order_status', 7)->where('pay_status', 1)->whereBetween('submit_time', [$start_time, $end_time])->count();
        //今日服务订单数
        $today_serve = Sorder::where('store_id', $store_id)->whereIn('order_status', [1, 2])->where('pay_status', 1)->whereBetween('submit_time', [$start_time, $end_time])->count();
        //升级订单数
        $today_level = Lorder::where('store_id', $store_id)->where('order_status', 1)->where('pay_status', 1)->where('apply_status', 0)->whereBetween('submit_time', [$start_time, $end_time])->count();

        $data = [
//            'user_id' => $user_id
            'avatar' => $storekeeper->avatar,
            'store_name' => $store->name,
            'today_goods' => $today_goods,
            'today_serve' => $today_serve,
            'today_level' => $today_level,
            'yesterday_earnings' => $yesterday_earnings,
            'today_earnings' => $today_earnings,
            'total_earnings' => $total_earnings,
            'earnings' => $storekeeper->earnings
        ];
        return jsonSuccess($data);
    }

    /**
     * 商品订单列表
     */
    public function gorderList(Request $request)
    {

        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        $order_status = $request->input('order_status');
        $orderModel = new Gorder();
//        $order = $orderModel->where()->
        $where['store_id'] = $storekeeper->store_id;
        if ($order_status != null) {
            $where['order_status'] = $order_status;
        }
        $order = $orderModel->getGoodsList($where);
        $list = [];
        if ($order) {
            $list = StoreGorderList::collection($order);
        }
        return jsonSuccess($list);
    }

    /**
     * 商品接单
     */
    public function gorderReceiving(Request $request)
    {
        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if ($storekeeper->is_storekeeper != 1 && $storekeeper->user_type != 4) {
            return jsonError('您不是该店店主');
        }
        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('参数获取失败');
        }
        $order = Gorder::where('id', $order_id)->lockForUpdate()->first();
        if (!$order) {
            return jsonError('数据获取失败');
        }
        if ($order->order_status == 7 && $order->pay_status == 1) {
            if ($order->delivery_method == 1) {
                $order->order_status = 1;
            }
            if ($order->delivery_method == 2) {
                $order->order_status = 3;
            }
            $result = $order->save();
            if ($result) {
                return jsonSuccess('接单成功');
            }
            return jsonError('接单失败');
        }
        return jsonError('该订单不能接单');


    }

    /**
     * 商品订单拒绝接单
     */
    public function gorderRefuse(Request $request)
    {
        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if ($storekeeper->is_storekeeper != 1 && $storekeeper->user_type != 4) {
            return jsonError('您不是该店店主');
        }

        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('参数获取失败');
        }

        $order = Gorder::where('id', $order_id)->lockForUpdate()->first();
        if (!$order) {
            return jsonError('订单获取失败');
        }

        if ($order->order_status == 7 && $order->pay_status == 1) {

            // 退款给用户(需要双向证书)
            $payModel = new WechatPayController($storekeeper->store_id);
            $transaction_id = $order->pay_sn;
            $refund_no = $order->order_sn;
            $total_fee = $order->total_price * 100;
            $refund_desc = '店主拒接您购买的商品订单';
//                $refundNotifyUrl = url('api/v1/refund/level');
            $result = $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
            //TODO 退还库存
//            $goods = Goods::where('')

            if ($result) {
                $order->order_status = 8;
                $order->pay_status = 2;
                $order->save();
                return jsonSuccess('拒绝订单成功');
            }
            return jsonError('退款失败，拒绝订单失败');
        }
        return jsonError('该订单不能被拒绝');


    }

    /**
     * 用户已取货
     */
    public function gorderClaim(Request $request)
    {
        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if ($storekeeper->is_storekeeper != 1 && $storekeeper->user_type != 4) {
            return jsonError('您不是该店店主');
        }

        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('参数获取失败');
        }

        $order = Gorder::where('id', $order_id)->lockForUpdate()->first();
        if (!$order) {
            return jsonError('订单获取失败');
        }

        if ($order->order_status == 3 && $order->pay_status == 1) {
            $order->order_status = 4;
            $result = $order->save();
            if ($result) {
                return jsonSuccess('确认用户取货成功');
            }
            return jsonError('确认用户取货失败');
        }
        return jsonError('该订单不能确认取货');

    }

    /**
     * 服务订单列表
     */
    public function sorderList(Request $request)
    {
        //判断用户是否登录
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $storekeeper_id]);
//        $order_status = $request->input('order_status');
        $type = $request->input('type');

        $order = new Sorder();
        if ($type == 1) {
            $where['order_status'] = 1;
            $order = $order->whereIn('order_status', [1, 2]);
        }
        if ($type == 2) {
            $order = $order->whereIn('order_status', [4, 5, 6]);
        }
        if ($type == 3) {
            $order = $order->whereIn('order_status', [3, 7]);
        }
        $order = $order->where('store_id', $user->store_id)->with(['user' => function ($query) {
            return $query->with('card');
        }])->with('orderServe')->with('beautician')->orderBy('submit_time', 'desc')->get();

        $list = ServeList::collection($order);
        return jsonSuccess($list);
    }

    /**
     * 审批列表
     */
    public function lorderList(Request $request)
    {
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $userModel = new Users();
        $user = $userModel->getInfo(['id' => $storekeeper_id]);
//        $order_status = $request->input('order_status');
        $type = $request->input('type');
        $lorderModel = new Lorder();
        if ($type == 1) {
            $where['order_status'] = 1;
        }
        if ($type == 2) {
            $where['order_status'] = 2;
        }
        $where['store_id'] = $user->store_id;
        $lorder = $lorderModel->getLevelList($where);
        $list = LevelOrderList::collection($lorder);
        return jsonSuccess($list);
    }

    /**
     * 同意申请
     */
    public function lorderAgree(Request $request)
    {
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if (!$storekeeper) {
            return jsonError('店主信息获取失败');
        }
        if ($storekeeper->is_storekeeper != 1 || $storekeeper->user_type != 4) {
            return jsonError('您不是店主');
        }
        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('参数获取失败');
        }
        try {
            $order = Lorder::where('id', $order_id)->first();
            if (!$order) {
                return jsonError('数据获取失败');
            }
            if ($order->order_status != 1 || $order->pay_status != 1) {
                return jsonError('该订单不能同意');
            }
            DB::beginTransaction();
            $order->order_status = 2;
            $order->save();
            //修改用户类型和状态
            $user = Users::where('id', $order->user_id)->first();
            if ($user->user_type < 2) {
                $user->user_type = 2;
            }
            $user->is_partner = 1;
            $user->level_id = $order->level_id;
            $user->save();
            DB::commit();
            return jsonMsg('同意申请成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return jsonSuccess('同意申请失败');
        }

    }

    /**
     * 拒绝申请
     */
    public function lorderRefuse(Request $request)
    {
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }

        $order_id = $request->input('order_id');
        if (!$order_id) {
            return jsonError('获取参数失败');
        }

        $storekeeper = Users::where('id', $storekeeper_id)->first();
        if ($storekeeper->is_storekeeper != 1 && $storekeeper->user_type != 4) {
            return jsonError('您不是店主');
        }
        try {
            DB::beginTransaction();
            $order = Lorder::where('id', $order_id)->lockForUpdate()->first();
            if (!$order) {
                return jsonError('订单数据获取失败');
            }
            if ($order->order_status != 1 || $order->pay_status != 1) {
                return jsonError('该订单不能拒绝');
            }
            //TODO 给用户退款
            $payModel = new WechatPayController($storekeeper->store_id);
            $transaction_id = $order->pay_sn;
            $refund_no = $order->order_sn;
            $total_fee = $order->total_price * 100;
            $refund_desc = '店主拒绝您申请的会员升级';
//                $refundNotifyUrl = url('api/v1/refund/level');
            $result = $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
            if ($result) {
                $order->order_status = 3;
                $order->save();
                DB::commit();
                return jsonSuccess($result);
            }
            return jsonError('微信退款失败,申请拒绝失败');
        } catch (\Exception $exception) {
            DB::rollBack();
            return jsonError('申请拒绝失败');
        }
    }

    /**
     * 清除登录缓存
     */
    public function adminClear(Request $request)
    {
        $storekeeper_id = $request->input('user_id');
        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if ($res != $storekeeper_id) {
            return jsonError('退出失败');
        }
        $result = Cache::forget($store_token);
        if ($result) {
            return jsonMsg('退出成功');
        }
        return jsonError('退出失败');
    }

    /**
     * 今日收益
     */
    public function earningsLog(Request $request)
    {
        $storekeeper_id = $request->input('user_id');
        if (!$storekeeper_id) {
            return jsonLoginError();
        }

        $store_token = $request->input('store_token');
        $res = Cache::get($store_token);
        if (!$res) {
            return jsonStoreError();
        }
        $earnings = EarningsLog::where('user_id', $storekeeper_id)->orderBy('add_date', 'desc')->get()->groupBy('add_date');
        $total = EarningsLog::where('user_id', $storekeeper_id)
            ->select(DB::raw('sum(earnings_amount) as total_amount'))
            ->orderBy('add_date', 'desc')
            ->groupBy('add_date')
            ->get();
//        $earnings = EarningsLog::select('id', 'title', 'created_at')
//            ->get()
//            ->groupBy(function($date) {
//                return Carbon::parse($date->created_at)->format('Y'); // grouping by years
//                //return Carbon::parse($date->created_at)->format('m'); // grouping by months
//            });

//        $earnings = $this->arraySort($earnings,'created_at');
//        dd($earnings);
        $arr = [];
        if ($earnings) {
            $i = 0;
            foreach ($earnings as $key => $val) {
                $arr[$i]['time'] = $key;
                $arr[$i]['total'] = $total[$i]['total_amount'];

                foreach ($val as $k => $value) {
                    $val[$k]['add_time'] = date('H:i', $value->add_time);
                }
                $arr[$i]['list'] = $val;
                $i++;
            }
        }

        return jsonSuccess($arr);
    }

}