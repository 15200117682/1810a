<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    //用户管理
    $router->resource('users', UsersController::class);

    //素材管理
    $router->get('/materadd', 'ImgController@materadd');//上传素材页面
    $router->post('/matermedo', 'ImgController@matermedo');//上传素材
    $router->resource('materlist', ImgController::class);//素材展示

    //菜单管理
    $router->get('/menuadd', 'MenuController@menuadd');
    $router->post('/menumedo', 'MenuController@menumedo');
    $router->resource('menulist', MenuController::class);//用户管理

    //群发消息
//    $router->resource('/mass', MassController::class);//群发消息
    $router->get('/mass/list', 'MassController@MassAll');
    $router->post('/MassAllAdd', 'MassController@MassAllAdd');

    //二维码渠道
    $router->get('/codeadd', 'CodeController@codeadd');//展示添加页面
    $router->post('/codemedo', 'CodeController@codemedo');//执行添加渠道
    $router->any('/code_ure', 'CodeController@code_ure');//执行添加渠道
    $router->resource('/codelist', CodeController::class);//渠道管理

    //标签管理
    $router->get('/tagadd', 'TagController@tagadd');//展示添加标签
    $router->post('/tagmedo', 'TagController@tagmedo');//展示添加标签
    $router->post('/maketag', 'TagController@maketag');//给用户打标签
    $router->get('/tag_del', 'TagController@tag_del');//标签信息
    $router->resource('/taglist', TagController::class);//渠道管理

    //第三周测试题
    $router->get('/san/sanadd', 'SanController@sanadd');//添加题目
    $router->any('/san/sanjia', 'SanController@sanjia');//题目入库
    $router->resource('/san/sanlist', SanController::class);//第三周


});
