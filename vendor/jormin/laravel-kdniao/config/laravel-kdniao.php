<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 快递鸟EBusinessID
    |--------------------------------------------------------------------------
    |
    | 快递鸟用户ID
    */

    'business_id' => env('KDNIAO_EBUSINESS_ID', null),

    /*
    |--------------------------------------------------------------------------
    | 快递鸟AppKey
    |--------------------------------------------------------------------------
    |
    | 快递鸟Api Key
    */

    'api_key' => env('KDNIAO_API_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | 是否开启调试模式
    |--------------------------------------------------------------------------
    | 调试模式下，快递鸟订阅接口地址：http://testapi.kdniao.cc:8081/api/dist
    | 线上模式下，快递鸟订阅接口地址：http://api.kdniao.cc/api/dist
    |
    */
    'debug' => env('APP_DEBUG', false),

];