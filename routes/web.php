<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get("/info",function (){
	phpinfo();
});//查看php配置



Route::get("/wechat/chat","Wechat\WechatController@getWechat");//首次接入测试
Route::post("/wechat/chat","Wechat\WechatController@WXEvent");//post接入测试
