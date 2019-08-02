<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadsController extends Controller
{
    public function upload(Request $request)
    {
        //1、使用laravel 自带的request类来获取一下文件
        $file = $request->file('file');

        if (!$file){
            return jsonError('请上传图片');
        }
        $filedir="uploads/images/";
        $imagesName=$file->getClientOriginalName(); //3、获取上传图片的文件名
        $extension = $file -> getClientOriginalExtension(); //4、获取上传图片的后缀名
        $newImagesName=md5(time()).".".$extension; //5、重新命名上传文件名字
        $file->move($filedir,$newImagesName);
        $filename = url($filedir.$newImagesName);
        return jsonSuccess($filename);
    }
    
}
