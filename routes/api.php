<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware'  => 'mock.user',
    'prefix' => 'v1',
    'namespace' => 'Api',
],function (Router $router) {
    $router->get('login', 'WeChatController@autoLogin')->middleware('wechat.oauth:snsapi_base');
    $router->get('register', 'WeChatController@autoRegister')->middleware('wechat.oauth:snsapi_base');
});

Route::group([
    'namespace' => 'Api',
    'prefix' => 'v1',
    'middleware'  => ['api','cross.http']
//    'middleware'  => ['wechat']
],function (Router $router) {
    $router->any('wechat/login','WeChatController@wechatLogin');
    $router->any('demo','DemoController@test');
    $router->any('wechat/register','WeChatController@wechatRegister');
});

Route::group([
    'namespace' => 'Api',
    'prefix' => 'v1',
    'middleware'  => ['api','cross.http','access.token']
//    'middleware'  => ['wechat']
],function (Router $router) {
    //
    //首页
    $router->get('home','HomeController@home');
    //公司介绍
    $router->get('introduction','HomeController@CompanyIntroduction');
    //操作引导
    $router->get('operation','HomeController@operationManual');
    //店铺风采
    $router->get('store/style','HomeController@storeStyle');
    //新品推荐
    $router->get('new/products','HomeController@newProducts');
    //最新活动
    $router->get('event/list','HomeController@eventList');
    //详情
    $router->get('event/detail','HomeController@eventDetail');
    //团队风采
    $router->get('mien','HomeController@teamMien');
    //店铺位置
    $router->get('location','HomeController@location');


    //分类列表
    $router->get('goods/category','GoodsController@categoryList');
    //商品列表
    $router->get('goods/list','GoodsController@goodsList');
    //商品详情
    $router->get('goods/detail','GoodsController@goodsDetail');
    //商品评论列表
    $router->get('goods/comment/list','GoodsController@goodsCommentList');
    //商品评论
    $router->post('goods/comment','GoodsController@goodsComment');
    //商品购买
    $router->post('goods/next','GoodsController@goodsNext');
    //商品确认
    $router->get('goods/confirm','GoodsController@goodsConfirm');
    //商品提交
    $router->post('goods/submit','GoodsController@goodsSubmit');
    //商品支付
    $router->post('goods/pay','GoodsController@goodsPay');
    //商品支付回调
    $router->post('goods/notify','GoodsController@goodsNotify');
    //补货商品订单
    $router->get('product/list','GoodsController@productList');

    //图文专栏列表
    $router->get('graphic/list','MaterialController@graphicList');
    //图文专栏详情
    $router->get('graphic/detail','MaterialController@graphicDetail');
    //宣传报道列表
    $router->get('report/list','MaterialController@reportList');
    //宣传报道详情
    $router->get('report/detail','MaterialController@reportDetail');
    //宣传报道点赞
    $router->post('report/like','MaterialController@reportLike');
    //百问百答列表
    $router->get('question/list','MaterialController@questionList');
    //百问百答详情
    $router->get('question/detail','MaterialController@questionDetail');
    //百问百答点赞
    $router->post('question/like','MaterialController@questionLike');
    //百问百答评论
    $router->post('question/comment','MaterialController@questionComment');
    //素材圈列表
    $router->get('material/list','MaterialController@materialList');
    //素材圈详情
    $router->get('material/detail','MaterialController@materialDetail');
    //素材圈点赞
    $router->post('material/like','MaterialController@materialLike');


    //用户购物车
    $router->get('cart/list','CartController@cartList');
    //添加购物车
    $router->post('cart/add','CartController@cartAdd');
    //购物车移除
    $router->post('cart/del','CartController@cartDelete');
    //购物车更新
    $router->post('cart/update','CartController@cartUpdate');
    //购物车信息保存
    $router->post('cart/next','CartController@cartNext');
    //购物车确认页面
    $router->get('cart/confirm','CartController@cartConfirm');
    //购物车订单提交
    $router->post('cart/submit','CartController@cartSubmit');


    //用户注册
    $router->post('user/binding','UsersController@mobileBinding');
    //发送短信
    $router->post('send/code','UsersController@sendCode');
    //用户登陆
    $router->post('user/login','UsersController@mobileLogin');
    //个人中心
    $router->get('my','UsersController@my');
    //我的团队
    $router->get('my/team','UsersController@team');
    //我的海报
    $router->get('my/poster','UsersController@poster');


    //会员卡列表
    $router->get('card/list','CardController@cardList');
    //会员卡订单生成
    $router->post('card/submit','CardController@cardSubmit');
    //会员卡订单支付
    $router->post('card/pay','CardController@cardPay');
    //会员卡支付回调
    $router->post('card/notify','CardController@cardNotify');


    //我的订单
    $router->get('order/my','OrderController@orderMy');
    //订单详情
    $router->get('order/detail','OrderController@orderDetail');
    //取消订单
    $router->post('order/cancel','OrderController@orderCancel');
    //支付订单
    $router->post('order/pay','OrderController@orderPay');
    //订单确认
    $router->post('order/confirm','OrderController@orderConfirm');
    //订单评论
    $router->post('order/comment','OrderController@orderComment');
    //删除订单
    $router->post('order/del','OrderController@orderDelete');
    //支付回调
    $router->post('order/notify','OrderController@orderNotify');


    //我的收益
    $router->get('earnings/my','EarningsController@earningsMy');
    //提现
    $router->post('earnings/withdraw','EarningsController@withdraw');


    //我的服务
    $router->get('serve/my','ServeController@serveMy');
    //服务详情
    $router->get('serve/detail','ServeController@serveDetail');
    //服务拒绝
    $router->post('serve/reject','ServeController@serveReject');
    //同意服务
    $router->post('serve/agree','ServeController@serveAgree');
    //申请订单
    $router->post('serve/apply','ServeController@serveApply');
    //服务评价
    $router->post('serve/comment','ServeController@serveComment');
    //删除订单
    $router->post('serve/del','ServeController@serveDelete');
    //服务订单
    $router->get('serve/order','ServeController@serveOrder');
    //确认服务订单
    $router->post('serve/confirm','ServeController@serveConfirm');
    //取消服务订单
    $router->post('serve/cancel','ServeController@serveCancel');
    //确认服务订单下一步
    $router->get('serve/next','ServeController@serveNext');
    //服务时间选择
    $router->get('serve/week','ServeController@serveWeek');
    //服务时间选择
    $router->get('serve/time','ServeController@serveTime');
    //服务支付
    $router->post('serve/pay','ServeController@servePay');
    //服务支付回调
    $router->post('serve/notify','ServeController@serveNotify');


    //签到页面
    $router->get('sign','SignController@sign');
    //签到
    $router->post('sign/submit','SignController@signSubmit');
    //签到排行
    $router->get('sign/rank','SignController@signRank');
    //评论排行
    $router->get('comment/rank','SignController@commentRank');


    //文件上传
    $router->post('uploads','UploadsController@upload');

    //设置页面
    $router->get('setting','SettingController@index');
    //设置提交
    $router->post('setting/submit','SettingController@settingSubmit');

    //地址列表
    $router->get('address/list','AddressController@list');
    //添加地址
    $router->post('address/create','AddressController@create');
    //地址修改
    $router->get('address/edit','AddressController@edit');
    //地址更新
    $router->post('address/update','AddressController@update');
    //默认地址
    $router->post('address/default','AddressController@default');
    //门店自提地址
    $router->get('address/store','AddressController@store');
    //删除地址
    $router->post('address/del','AddressController@del');

    //等级申请
    $router->get('level','LevelController@levelIndex');
    //提交订单
    $router->post('level/submit','LevelController@levelSubmit');
    //等级订单支付
    $router->post('level/pay','LevelController@levelPay');
    //等级支付回调
    $router->post('level/notify','LevelController@levelNotify');


    //店铺列表
    $router->get('store/list','StoreController@storeList');
    //店铺详情
    $router->get('store/detail','StoreController@storeDetail');
    //店铺服务
    $router->get('store/serve/list','StoreController@serveList');
    //店铺评论列表
    $router->get('store/comment/list','StoreController@commentList');
    //店铺美容师
    $router->get('store/beautician/list','StoreController@beauticianList');
    //美容师详情
    $router->get('store/beautician/detail','StoreController@beauticianDetail');
    //店铺后台登录
    $router->post('store/login','StoreController@adminLogin');
    //店铺登录
    $router->post('store/wechat/login','StoreController@wechatLogin');
    //店铺后台
    $router->get('store/admin','StoreController@admin');
    //登录缓存清理
    $router->post('store/clear','StoreController@adminClear');
    //订单列表
    $router->get('store/gorder/list','StoreController@gorderList');
    //接单
    $router->post('store/gorder/receiving','StoreController@gorderReceiving');
    //拒绝
    $router->post('store/gorder/refuse','StoreController@gorderRefuse');
    //客户已取货
    $router->post('store/gorder/claim','StoreController@gorderClaim');
    //服务订单列表
    $router->get('store/sorder/list','StoreController@sorderList');
    //同意等级升级
    $router->post('store/lorder/agree','StoreController@lorderAgree');
    //拒绝等级升级
    $router->post('store/lorder/refuse','StoreController@lorderRefuse');
    //店铺等级列表
    $router->get('store/lorder/list','StoreController@lorderList');
    //店铺今日收益
    $router->get('store/earnings/log','StoreController@earningsLog');
    //美容师评论列表
    $router->get('beautician/comment/list','StoreController@beauticianCommentList');

    //快递获取
    $router->get('get/express','ExpressController@getExpress');


    $router->get('wechat/config','WeChatController@getSignPackage');
    //商家入驻分类
    $router->get('merchant/category','MerchantController@category');
    //商家入驻列表
    $router->get('merchant','MerchantController@list');
    //详情
    $router->get('merchant/detail','MerchantController@detail');
    //授权查询
    $router->get('accredit/search','AccreditController@search');
    //分享获取用户ID
    $router->get('share','ShareController@share');
    //测试demo
    $router->get('apply/demo','ApplyController@demo');
    //测试demo2
    $router->get('apply/demo2','ApplyController@demo2');

    $router->get('apply/open','ApplyController@open');

    $router->get('apply/close','ApplyController@close');


    //设备主页
    $router->get('device','DeviceController@index');
    //设备订单提交
    $router->post('device/submit','DeviceController@submit');

    $router->post('device/pay','DeviceController@pay');

    $router->post('device/notify','DeviceController@notify');
    //广告
    $router->get('ad','HomeController@ad');

    //定时任务
    $router->get('auto','AutoController@auto');

});