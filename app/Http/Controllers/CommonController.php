<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function upload(Request $request)
    {
        //判断请求中是否包含name=img的上传文件
        if (!$request->hasFile('image')) {
            return '请选择上传图片';
        }
        // 判断图片上传中是否出错
        $file = $request->file('image');
        if (!$file->isValid()) {
            return '上传图片失败';
        }
        //$img_path = $file -> getRealPath(); // 获取临时图片绝对路径
        $entension = $file -> getClientOriginalExtension(); //  上传文件后缀
        $filename = date('YmdHis').mt_rand(100,999).'.'.$entension;  // 重命名图片
        $path = $file->move(public_path().'/uploads/images/',$filename);  // 重命名保存
        $img_path = 'uploads/images/'.$filename;
        return response()->json([
            'errno'=>0,
            'data' =>
                [
                    url($img_path),
                ]
        ]);
    }
}