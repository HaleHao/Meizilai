<?php

namespace App\Logic;


use App\Models\EarningsLog;
use App\Models\MemberLevel;
use App\Models\Users;
use Illuminate\Support\Facades\Log;

class OrderLogic
{

    public function save($data)
    {

        $users = Users::where('id',$data['user_id'])->first();

        if ($users) {

            $first_user_id = $users->first_user_id;

            $second_user_id = $users->second_user_id;

            if ($first_user_id != 0) {

                //一级分销用户收益
                $first_user = Users::where('id', $first_user_id)->lockForUpdate()->first();

                if ($first_user->level_id) {
                    //计算获取收益
                    $first_level = MemberLevel::find($first_user->level_id);

                    $earnings_amount = $data['order_amount'] * $first_level->ratio;

                    $first_user->earnings = $first_user->earnings + $earnings_amount;

                    Log::info($first_user);

                    $first_user->save();

                    //保存收益记录
                    $shopOrderDeatis = new EarningsLog();
                    //名称
                    $shopOrderDeatis->event_name = $data['name'];
                    //是否开启分销
                    $shopOrderDeatis->transaction_type = $data['transaction_type'];
                    //店铺ID
                    $shopOrderDeatis->store_id = $data['store_id'];
                    //保存用户收益信息
                    $shopOrderDeatis->user_id = $first_user->id;
                    //店铺ID
                    $shopOrderDeatis->add_time = time();

                    $shopOrderDeatis->add_date = date('Y-m-d');

                    $shopOrderDeatis->order_id = $data['order_id'];

                    $shopOrderDeatis->event_type = $data['event_type'];

                    $shopOrderDeatis->earnings_amount = $earnings_amount;

                    $shopOrderDeatis->order_amount = $data['order_amount'];

                    Log::info($shopOrderDeatis);

                    $shopOrderDeatis->save();

                    //判断该用户等级是否最高的
                    if ($first_level->level != 3) {
                        if ($second_user_id != 0) {
                            //二级分销
                            $second_user = Users::where('id', $second_user_id)->lockForUpdate()->first();
                            $second_level = MemberLevel::find($first_user->level_id);
                            //判断下一级分销等级大于上一级等级
                            if ($first_level->level < $second_level->level) {

                                $ratio = 0.4 - $first_level->ratio;

                                $second_earnings = $second_user->earnings + $data['order_amount'] * $ratio;

                                $second_user->earnings = $second_earnings;

                                Log::info($second_user);

                                $second_user->save();
                                //保存收益记录
                                $shopOrderDeatis1 = new EarningsLog();
                                //名称
                                $shopOrderDeatis1->event_name = $data['name'];
                                //是否开启分销
                                $shopOrderDeatis1->transaction_type = $data['transaction_type'];
                                //店铺ID
                                $shopOrderDeatis1->store_id = $data['store_id'];
                                //保存用户收益信息
                                $shopOrderDeatis1->user_id = $first_user_id->id;
                                //店铺ID
                                $shopOrderDeatis1->add_time = time();

                                $shopOrderDeatis1->add_date = date('Y-m-d');

                                $shopOrderDeatis1->order_id = $data['order_id'];

                                $shopOrderDeatis1->event_type = $data['event_type'];

                                $shopOrderDeatis1->earnings_amount = $second_earnings;

                                $shopOrderDeatis1->order_amount = $data['order_amount'];

                                Log::info($shopOrderDeatis1);

                                $shopOrderDeatis1->save();
                            }

                        }
                    }
                }
            }

        }
        return true;
    }

}