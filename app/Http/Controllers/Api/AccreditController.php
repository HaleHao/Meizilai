<?php

namespace App\Http\Controllers\Api;

use App\Models\Accredit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccreditController extends Controller
{
    //授权查询
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        if (!$keyword){
            return jsonError('请输入查询的关键词');
        }
        $type = $request->input('type');
        $accreditModel = new Accredit();
        $accredit = [];
        if ($type == 'mobile'){
            $accredit = $accreditModel->where('mobile',$keyword)->first();
        }
        if ($type == 'username'){
            $accredit = $accreditModel->where('username',$keyword)->first();
        }
        if ($type == 'id_card'){
            $accredit = $accreditModel->where('id_card',$keyword)->first();
        }

        $data = [];
        if($accredit){
            $data = [
                'contract' => url('uploads/'.$accredit->contract),
            ];
        }
        return jsonSuccess($data);
    }
}
