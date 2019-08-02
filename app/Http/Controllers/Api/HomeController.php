<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EventDetail;
use App\Http\Resources\EventList;
use App\Http\Resources\GoodsList;
use App\Http\Resources\ServeList;
use App\Http\Resources\ServeOrderList;
use App\Http\Resources\StoreServeList;
use App\Http\Resources\StoreStyle;
use App\Models\Ad;
use App\Models\Banner;
use App\Models\CompanyIntroduction;
use App\Models\Event;
use App\Models\Goods;
use App\Models\Notice;
use App\Models\OperationManual;
use App\Models\Store;
use App\Models\StoreServe;
use App\Models\TeamMien;
use App\Http\Resources\BannerList;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends CommonController
{


    /**
     * 首页
     */
    public function home(Request $request)
    {
//        dd($request->input('user_id'));
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        //轮播图
        $bannerModel = new Banner();
        $banner = $bannerModel->getList();
        $banner_list = [];
        if ($banner){
            $banner_list = BannerList::collection($banner);
        }
        //热销产品
        $goodsModel = New Goods();
        $where = [
            'is_hot' => 1,
            'is_put' => 1,
            'goods_type' => 0
        ];
        $goods = $goodsModel->getList($where);
        $goods_list = [];
        if ($goods){
            $goods_list = GoodsList::collection($goods);
        }

        $is_mobile = 0;
        $is_card = 0;
        $arr = [];
        if ($user_id){
            $user = Users::where('id',$user_id)->first();
            if ($user->mobile){
                $is_mobile = 1;
            }
            if ($user->card_id){
                $is_card = 1;
            }
            $arr = [
                'is_card' => $is_card,
                'is_mobile' => $is_mobile
            ];
        }

        //公告
        $notice = Notice::where('is_show',1)->orderBy('sort','asc')->get();

        $data = [
            'banner' => $banner_list,
            'goods' => $goods_list,
            'user' => $arr,
            'notice' => $notice
        ];
        return jsonSuccess($data);
    }


    public function location(Request $request){
        $store_id = $request->input('store_id');
        if ($store_id){
            $store = Store::where('id',$store_id)->first();
            if ($store){
                $data = [
                    'lat' => $store->lat,
                    'lng' => $store->lng,
                    'address' => $store->province.$store->city.$store->district.$store->address,
                    'name' => '我的位置'
                ];
                return jsonSuccess($data);
            }
            return jsonError('店铺获取失败');
        }
        return jsonError('店铺ID获取失败');
    }
    /**
     * 公司介绍
     */
    public function companyIntroduction()
    {
        $company = new CompanyIntroduction();
        $detail = $company->getDetail();
        if ($detail){
            return jsonSuccess($detail);
        }
        return jsonError('Data Error');
    }

    /**
     * 操作引导
     */
    public function operationManual()
    {
        $operation = new OperationManual();
        $detail = $operation->getDetail();
        if ($detail){
            return jsonSuccess($detail);
        }
        return jsonError('Data Error');
    }

    /**
     * 店铺风采
     */
    public function storeStyle()
    {
        $store = new Store();
        $where = [
            'is_lock' => 0
        ];
        $result = $store->getList($where);
        $list = [];
        if ($result){
            $list = StoreStyle::collection($result);
        }
        return jsonSuccess($list);
    }

    /**
     * 团队风采
     */
    public function teamMien()
    {
        $team = new TeamMien();
        $detail = $team->getDetail();
        if ($detail){
            return jsonSuccess($detail);
        }
        return jsonError('Data Error');
    }

    /**
     * 新品推荐
     */
    public function newProducts(Request $request)
    {
        $store_id = $request->input('store_id',4);
        $serve = StoreServe::where('store_id',$store_id)->orderBy('updated_at','desc')->get();
        $goods = Goods::where('is_new',1)->where('is_put',1)->orderBy('updated_at','desc')->get();
        $data['serve'] = [];
        if ($serve){
            $data['serve'] = StoreServeList::collection($serve);
        }
        $data['goods'] = [];
        if ($goods){
            $data['goods'] = GoodsList::collection($goods);
        }

        return jsonSuccess($data);
    }

    /**
     * 最新活动
     */
    public function eventList()
    {
        $event = Event::where('is_show',1)->orderBy('sort','asc')->orderBy('created_at','desc')->get();
        $data = [];
        if ($event){
            $data = EventList::collection($event);
        }
        return jsonSuccess($data);
    }

    //活动详情
    public function eventDetail(Request $request)
    {
        $event_id = $request->input('event_id');
        if (!$event_id){
            return jsonError('参数获取失败');
        }
        $event = Event::where('id',$event_id)->where('is_show',1)->first();
        if (!$event){
            return jsonError('活动获取失败');
        }
        $data = [];
        if ($event){
            $data = EventDetail::make($event);
        }
        return jsonSuccess($data);
    }


    //广告页面
    public function ad()
    {
        $ad = Ad::where('is_show', 1)->orderBy('sort', 'asc')->take(config('ad_num',3))->get();
        $list = [];
        if ($ad) {
            foreach ($ad as $val) {
                if ($val->img_url) {
                    $list[] = url('uploads/'.$val->img_url);
                }
            }
        }
        return jsonSuccess($list);
    }


}
