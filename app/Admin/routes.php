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
    $router->get('/users', 'UsersController@index')->name('admin.home');//用户管理

    //素材管理
    $router->get('/materadd', 'ImgController@materadd')->name('admin.Img');//上传素材页面
    $router->post('/matermedo', 'ImgController@matermedo')->name('admin.Img');//上传素材
    $router->get('/materlist', 'ImgController@index')->name('admin.Img');//素材展示

    //菜单管理
    $router->get('/menuadd', 'MenuController@menuadd')->name('admin.home');//用户管理
    $router->post('/menumedo', 'MenuController@menumedo')->name('admin.home');//用户管理
    $router->get('/menulist', 'MenuController@index')->name('admin.home');//用户管理

});
