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



});
