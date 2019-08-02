<?php


namespace App\Service;

class UploadService{

    public function upload($file)
    {
        //1、用laravel 自带的request类来获取一下文件
        $filedir="uploads/images/";
        $imagesName=$file->getClientOriginalName(); //3、获取上传图片的文件名
        $extension = $file -> getClientOriginalExtension(); //4、获取上传图片的后缀名
        $newImagesName = date('HmdHis').md5(time().rand(100000,999999).rand(1,999999)).".".$extension; //5、重新命名上传文件名字
        $file->move($filedir,$newImagesName);
        $filename = $filedir.$newImagesName;
        return $filename;
    }
}