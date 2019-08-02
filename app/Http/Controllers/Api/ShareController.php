<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Goods;
use App\Models\Material;
use App\Models\Question;
use App\Models\Report;
use App\Models\Users;
use Illuminate\Http\Request;

class ShareController extends CommonController
{
    //分享
    public function share(Request $request)
    {
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }
        $user = Users::where('id',$user_id)->first();
        $type = $request->input('type');
        if (!$type){
            return jsonError('获取类型失败');
        }
        $url = $request->input('url');
        if (!$url){
            return jsonError('获取url失败');
        }

        $id = $request->input('id');
        if ($type == 1){
            $goods = Goods::where('id',$id)->first();
            $data = [
                'title' => $goods->name,
                'desc' => $goods->title,
                'img_url' => url('uploads/'.$goods->cover_url),
                'url' => config('APP_URL').'ProductDetail?id='.$id.'&store_id='.$user->store_id.'&first_user_id='.$user_id,
            ];
        }
        if ($type == 2){
            $question = Question::where('id',$id)->first();
            $data = [
                'title' => $question->title,
                'desc' =>$question->title,
                'img_url' => '',
                'url' => config('APP_URL').'answers?id='.$id.'&store_id='.$user->store_id.'&first_user_id='.$user_id,
            ];
        }
        if ($type == 3){
            $material = Material::where('id',$id)->first();
            $data = [
                'title' => $material->title,
                'desc' =>$material->title,
                'img_url' => '',
                'url' => config('APP_URL').'materialDetail?id='.$id.'&store_id='.$user->store_id.'&first_user_id='.$user_id,
            ];
        }
        if ($type == 4){
            $report = Report::where('id',$id)->first();
            $data = [
                'title' => $report->title,
                'desc' => $report->title,
                'img_url' => url('uploads/'.$report->cover_url),
                'url' => config('APP_URL').'actDetail?id='.$id.'store_id='.$user->store_id.'&first_user_id='.$user_id,
            ];
        }
        if ($type == 5){
            $event = Event::where('id',$id)->first();
            $data = [
                'title' => $event->title,
                'desc' => $event->title,
                'img_url' => url('uploads/'.$event->cover_url),
                'url' => config('APP_URL').'actDetail?id='.$id.'store_id='.$user->store_id.'&first_user_id='.$user_id,
            ];
        }
        $data['store_id'] = $user->store_id;
        $data['first_user_id'] = $user_id;
        return jsonSuccess($data);
    }
}