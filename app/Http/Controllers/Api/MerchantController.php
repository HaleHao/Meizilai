<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MerchantDetail;
use App\Http\Resources\MerchantList;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MerchantController extends CommonController
{
    //商家分类
    public function category()
    {
        $category = MerchantCategory::where('is_show',1)->orderBy('order','asc')->get();
        return jsonSuccess($category);
    }

    public function list(Request $request)
    {
        $user_lng = $request->input('longitude', "114.040833");
        $user_lat = $request->input('latitude', "22.617972");

        $city = $request->input('city');
        if (!$city){
            $key = config('MAP_KEY','KKXBZ-ZDEWU-VQIV5-4DFHT-A447H-LJFDY');
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$user_lat.",".$user_lng."&key=".$key."&get_poi=1";
            $html = file_get_contents($url);
            $result = json_decode($html);
            $city = data_get(data_get(data_get($result,'result'),'address_component'),'city');
        }
        $merchant = new Merchant();
        $category_id = $request->input('category_id');
        if ($category_id){
            $merchant = $merchant->where('category_id',$category_id);
        }

        //截取前两个字符
        if ($city){
            $city_str = mb_substr($city,0,2,'UTF-8');
            $merchant = $merchant->where('city','like','%'.$city_str.'%');
        }

        $keyword = $request->input('keyword');
        if ($keyword){
            $merchant = $merchant->where('name','like','%'.$keyword.'%');
        }

        $type = $request->input('type');

        if ($type == 2){
            $merchant = $merchant->orderBy('grade','desc');
        }

        if ($type == 3){
            $merchant = $merchant->orderBy('created_at','desc');
        }

        $user_lng = $request->input('lng',"114.040833");
        $user_lat = $request->input('lat',"22.617972");

        $result = $merchant->get();
        $list = [];
        if ($result){
            foreach ($result as  $key => $value){
                $distance = $this->getDistance($user_lng,$user_lat,$value->lng,$value->lat);
                $distance = round((int)$distance/1000,1);
                $result[$key]['distance'] = $distance;
            }

//            dd($result);
            if ($type == 1){
                $result = $this->arraySort($result->toArray(),'distance');
            }
//            if ($result)
           foreach ($result as $value){
                $list[] =  [
                    'id' => data_get($value,'id'),
                    'name' => data_get($value,'name'),
                    'cover_url' => url('uploads/'.data_get($value,'cover_url')),
                    'address' => data_get($value,'province').data_get($value,'city').data_get($value,'district').data_get($value,'address'),
                    'grade' =>data_get($value,'grade'),
                    'lat' => data_get($value,'lat'),
                    'lng' => data_get($value,'lng'),
                    'distance' => data_get($value,'distance')
                ];
           }
        }
        $data = [
            'list' => $list,
            'city' => $city
        ];
        return jsonSuccess($data);
    }

    //二维数组排序
    public function arraySort($array, $keys, $sort = SORT_ASC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    public function detail(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        if (!$merchant_id){
            return jsonError('参数获取失败');
        }
        $merchant = Merchant::where('id',$merchant_id)->first();
        if (!$merchant){
            return jsonError('商家获取失败');
        }
        $data = MerchantDetail::make($merchant);

        return jsonSuccess($data);
    }

}
