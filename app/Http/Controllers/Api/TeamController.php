<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TeamList;
use App\Models\Users;
use Illuminate\Http\Request;


class TeamController extends CommonController
{

    //我的团队
    public function myTeam(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }


        $userModel = new Users();
        $level_id = $request->input('level_id');
        $user = $userModel->getInfo(['id' => $user_id]);
        if ($user->level_id){
            $where['super_id'] = $user_id;
            if ($level_id){
                $where['level_id'] = $level_id;
            }
            $result = $userModel->getTeam($where);
            $list = [];
            if ($result){
                $list = TeamList::collection($list);
            }
            return jsonSuccess($list);
        }
        return jsonError('您不是共享合伙人');
    }

}
