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

/**
 * curl post请求
 * @param $url
 * @param $post_data
 * @return bool|string
 */

function curlPost($url,$post_data)
{
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL,$url);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $data;
}