<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\Store;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AddressController extends CommonController
{
    //地址列表
    public function list(Request $request)
    {
//        $user_id = $this->getUserId();
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $address = Address::where('user_id',$user_id)->get();
        return jsonSuccess($address);
    }

    //创建地址
    public function create(Request $request)
    {
//        $user_id = $this->getUserId();
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $consignee = $request->input('consignee');
        if (!$consignee){
            return jsonError('请填写收货人');
        }

        $mobile = $request->input('mobile');
        if (!$mobile){
            return jsonError('请填写手机号码');
        }

        $province = $request->input('province');
        $city = $request->input('city');
        $district = $request->input('district');
        if (!$province || !$city || !$district){
            return jsonError('请选择所在区域');
        }
        $address = $request->input('address');
        if (!$address){
            return jsonError('请填写详细地址');
        }
        $is_default = $request->input('is_default',0);
        try{
            DB::beginTransaction();
            $result = Address::create([
                'user_id' => $user_id,
                'consignee' => $consignee,
                'mobile' => $mobile,
                'province' => $province,
                'city' => $city,
                'district' => $district,
                'address' => $address,
                'is_default' => $is_default
            ]);
            if ($is_default == 1){
                //将其他的收获地址改为0
                Address::where('user_id',$user_id)->where('id','!=',$result->id)->where('is_default',1)->update([
                    'is_default' => 0
                ]);
                Users::where('id',$user_id)->update([
                    'address_id' => $result->id
                ]);
            }

            DB::commit();
            return jsonMsg('新增地址成功');
        }catch (\Exception $exception) {
            DB::rollBack();
            return jsonError('新增地址失败');
        }
    }

    //修改地址
    public function edit(Request $request)
    {

        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $address_id = $request->input('address_id');
        if ($address_id ){
            $address = Address::where('user_id',$user_id)->where('id',$address_id)->first();
            if ($address){
                return jsonSuccess($address);
            }
            return jsonSuccess('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    //更新地址
    public function update(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $address_id = $request->input('address_id');
        if (!$address_id){
            return jsonError('地址ID获取失败');
        }
        $is_default = $request->input('is_default',0);
        try{
            DB::beginTransaction();
            $ad = Address::where('user_id',$user_id)->where('id',$address_id)->first();

            $consignee = $request->input('consignee');
            if ($consignee){
                $ad->consignee = $consignee;
            }

            $mobile = $request->input('mobile');
            if ($mobile){
                $ad->mobile = $mobile;
            }

            $province = $request->input('province');
            if ($province){
                $ad->province = $province;
            }

            $city = $request->input('city');
            if ($city){
                $ad->city = $city;
            }

            $district = $request->input('district');
            if ($district){
                $ad->district = $district;
            }

            $address = $request->input('address');
            if ($address){
                $ad->address = $address;
            }

            $ad->save();
            if ($is_default == 1){
                //将其他的收获地址改为0
                Address::where('user_id',$user_id)->where('id','!=',$ad->id)->where('is_default',1)->update([
                    'is_default' => 0
                ]);
                Users::where('id',$user_id)->update([
                    'address_id' => $ad->id
                ]);
            }
            DB::commit();
            return jsonMsg('地址修改成功');
        }catch (\Exception $exception) {
            DB::rollBack();
            return jsonError('地址修改失败');
        }
    }

    //设置默认
    public function default(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $address_id = $request->input('address_id');
        if ($address_id){
            try {
                DB::beginTransaction();
                $ad = Address::where('user_id', $user_id)->where('id', $address_id)->first();
                $ad->is_default = 1;
                $ad->save();
                Address::where('user_id', $user_id)->where('id', '!=', $ad->id)->where('is_default', 1)->update([
                    'is_default' => 0
                ]);
                Users::where('id', $user_id)->update([
                    'address_id' => $ad->id
                ]);
                DB::commit();
                return jsonMsg('默认地址修改成功');
            }catch(\Exception $exception){
                DB::rollBack();
                return jsonError('默认地址修改成功');
            }
        }
        return jsonError('参数获取失败');
    }

    //删除地址
    public function del(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $address_id = $request->input('address_id');
        if ($address_id){
           $result = Address::where('id',$address_id)->delete();
           if ($result){
               return jsonMsg('删除成功');
           }
           return jsonError('删除失败');
        }
        return jsonError('参数获取失败');
    }

    //门店自提地址
    public function store(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

//        $user = Users::where('id',$user_id)->first();
        $user = Users::where('id',$user_id)->first();
        if (!$user){
            return jsonLoginError();
        }

        $store = Store::where('id',$user->store_id)->first();
        $data[] = [
                'name' => $store->name,
                'province' => $store->province,
                'city' => $store->city,
                'district' => $store->district,
                'address' => $store->address
        ];
        return jsonSuccess($data);
    }

}
