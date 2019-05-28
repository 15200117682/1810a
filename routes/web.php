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

//登陆页面
Route::any("/login","Login\LoginController@index");//登陆
Route::any("/login/loginadd","Login\LoginController@loginadd");//登陆验证
Route::any("/login/code","Login\LoginController@code");//登陆验证
Route::any("/login/wechat","Login\LoginController@wechat");//登陆验证

Route::get("/wechat/chat","Wechat\WechatController@getWechat");//首次接入测试
Route::post("/wechat/chat","Wechat\WechatController@WXEvent");//post接入测试
Route::any("/wechat/openid","Wechat\WechatController@userList");//post接入测试

Route::get("/love","Love\LoveController@getWechat");
Route::post("/love","Love\LoveController@WXEvent");

Route::any("/wechat/getAccessToken","Wechat\WechatController@getAccessToken");//获取access_token

Route::any("/wechat/menu","Wechat\WechatController@menu");//自定义菜单

Route::any("/wechat/materadd","Wechat\WechatController@materadd");//添加临时素材
Route::any("/wechat/materlist","Wechat\WechatController@materlist");//获取临时素材

//模拟测试
Route::get("/ceshi/getce","CeShi\CeShiController@getCe");//首次接入测试
Route::post("/ceshi/getce","CeShi\CeShiController@getWechat");//post接入测试
Route::get("/ceshi/access_token","CeShi\CeShiController@AccessToken");//access_token
Route::get("/ceshi/auth","CeShi\CeShiController@auth");//网页授权
Route::get("/ceshi/authpage","CeShi\CeShiController@authpage");//网页授权
Route::any("/ceshi/biaoqian{name?}","CeShi\CeShiController@biaoqian");//标签添加

Route::get("/ceshi/index","CeShi\CeShiController@index");

Route::get("/ceshi/mass","CeShi\CeShiController@mass");//9点定时群发消息

Route::any("/ceshi/button","CeShi\CeShiController@button");//网页授权


//第三周测试
Route::get("/san/getsan","San\SanController@getSan");//首次接入测试
Route::post("/san/getsan","San\SanController@getEvent");//post接入测试


//jssdk
Route::any("/jsdk","Jsdk\JsdkController@jsdk");//jssdk





