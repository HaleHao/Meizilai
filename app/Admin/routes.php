<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    //轮播图管理
    $router->resource('banner','BannerController');
    //商品管理
    $router->resource('goods','GoodsController');
    //分类管理
    $router->resource('category','CategoryController');
    //公司介绍
    $router->resource('introduction','CompanyIntroductionController');
    //操作引导
    $router->resource('operation','OperationManualController');
    //团队风采
    $router->resource('mien','TeamMienController');
    //店铺列表
    $router->resource('store','StoreController');
    //店铺服务
    $router->resource('serve','ServeController');
    //用户绑定店铺
    $router->post('store/bing/{id}','StoreController@bing')->name('admin.store.bing');

    $router->resource('store','StoreController');
    //店铺信息修改
    $router->resource('info','StoreInfoController');
    //素材圈管理
    $router->resource('material','MaterialController');
    //百问百答
    $router->resource('question','QuestionController');
    //图文专栏
    $router->resource('graphic','GraphicController');
    //宣传报道
    $router->resource('report','ReportController');
    //会员卡管理
    $router->resource('member/card','MemberCardController');
    //会员等级管理
    $router->resource('member/level','MemberLevelController');
    //用户管理
    $router->resource('users','UsersController');

    $router->get('users/beautician/{id}','UsersController@beautician')->name('admin.users.beautician');
    //用户收益归零
    $router->get('users/zero','UsersController@zero')->name('admin.users.zero');
    //商品订单管理
    $router->resource('gorder','GorderController', [
        'names' => [
            'show' => 'admin.gorder.show',
            'index' => 'admin.gorder.index'
        ]
    ] );
    //快递发货
    $router->post('gorder/deliver/{id}','GorderController@deliver')->name('admin.gorder.deliver');
    //确认接单
    $router->get('gorder/receiving/{id}','GorderController@receiving')->name('admin.gorder.receiving');
    //拒绝接单
    $router->get('gorder/reject/{id}','GorderController@reject')->name('admin.gorder.reject');
    //确认用户取货
    $router->get('gorder/claim/{id}','GorderController@claim')->name('admin.gorder.claim');

    //服务订单管理
    $router->resource('sorder','SorderController',[
        'names' => [
            'show' => 'admin.sorder.show',
            'index' => 'admin.sorder.index'
        ]
    ]);
    //会员升级订单
    $router->resource('lorder','LorderController',[
        'names' => [
            'show' => 'admin.lorder.show',
            'index' => 'admin.lorder.index'
        ]
    ]);
    $router->get('lorder/agree/{id}','LorderController@agree')->name('admin.lorder.agree');

    $router->get('lorder/reject/{id}','LorderController@reject')->name('admin.lorder.reject');

    //会员卡订单
    $router->resource('corder','CorderController',[
        'names' => [
            'show' => 'admin.corder.show',
            'index' => 'admin.corder.index'
        ]
    ]);

    $router->resource('merchant/category','MerchantCategoryController');

    $router->resource('merchant','MerchantController');

    $router->resource('event','EventController');

    $router->resource('accredit','AccreditController');

    $router->resource('product','ProductController');

    $router->resource('notice','NoticeController');

    $router->get('device/open','DeviceController@open')->name('admin.device.open');

    $router->get('device/close','DeviceController@close')->name('admin.device.close');

    $router->resource('device/log','DeviceLogController');

    $router->resource('device','DeviceController');

    $router->resource('company/log','CompanyLogController');

    $router->resource('company','CompanyController');

    $router->post('company/earnings','CompanyController@earnings')->name('admin.company.earnings');

    $router->resource('ad','AdController');

});
