<?php

namespace App\Http\Controllers\Jsdk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JsdkController extends Controller
{
    public function jsdk(){
        $jsapiTicket=getJsapi_ticket();//jsapi_ticket

        /**组装签名**/
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";//动态获取访问地址
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $appId="wx3b19791119b8d948";
        $nonceStr =createNonceStr();//随机数
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        var_dump($appId);
        var_dump($timestamp);
        var_dump($nonceStr);
        var_dump($signature);exit;

        return view("jsdk.jsdk",[
            'appId'=>$appId,
            'timestamp'=>$timestamp,
            'nonceStr'=>$nonceStr,
            'signature'=>$signature
        ]);
    }

}
