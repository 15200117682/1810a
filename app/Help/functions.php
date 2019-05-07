<?php
use Illuminate\Support\Facades\Redis;

//获取access_token
function getAccessToken()
{
    $key = 'access_token';
    $token = Redis::get($key);
    if ($token) {
        //有缓存返回缓存数据
    } else {
        //无缓存调用接口获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . env('WX_APPID') . "&secret=" . env('WX_APPSECRET');//调接口
        $response = file_get_contents($url);
        $arr = json_decode($response, true);//转换为数组
        Redis::set($key, $arr['access_token']);// 存缓存
        Redis::expire($key, 3600);
        $token = $arr['access_token'];
    }
    return $token;
}

//获取用户基本信息
function openId($openId){
    //获取access
    $access=getAccessToken();
    $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$openId&lang=zh_CN";
    $count=file_get_contents($url);//流接受数据
    $u=json_decode($count,true);//转换数据为数组类型
    return $u;//返回数据
}