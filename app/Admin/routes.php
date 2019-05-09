<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/materadd', 'ImgController@materadd')->name('admin.Img');//上传素材页面
    $router->post('/matermedo', 'ImgController@matermedo')->name('admin.Img');//上传素材
    $router->get('/materlist', 'ImgController@index')->name('admin.Img');//素材展示

});
