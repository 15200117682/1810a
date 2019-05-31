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

        return view("jsdk.jsdk",[
            'appId'=>$appId,
            'timestamp'=>$timestamp,
            'nonceStr'=>$nonceStr,
            'signature'=>$signature
        ]);
    }

    public function pay(){
        //调用微信接口
        $nouce =createNonceStr();
        //订单号
        $out_trade_no = "10a".date("YmdHi").rand(1000,9999);//订单号
        $appid = 'wxd5af665b240b75d4';//企业号
        $mch_id = '1500086022';//微信支付分配的商户号
        $nonce_str = createNonceStr();//随机数
        $notify_url = "http://yuan.qiong001.com/paycode"; //支付成功后异步通知地址
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $total_fee = 1; //钱 单位是分
        $trade_type = "NATIVE";//签名类型
        $body = "ys测试";//订单名称

        $signArr = [
            'appid'=>$appid,//appid
            'body'=>$body,//订单名称
            'mch_id'=>$mch_id,//微信支付分配的商户号
            'nonce_str'=>$nonce_str,//随机数
            'notify_url'=>$notify_url,//回调地址
            'out_trade_no'=>$out_trade_no,//商户系统内部订单号
            'spbill_create_ip'=>$spbill_create_ip,
            'total_fee'=>$total_fee,//金钱
            'trade_type'=>$trade_type,//签名类型
        ];
        //生成签名
        //签名步骤一：按字典序排序参数
        ksort($signArr);
        $string = $this->ToUrlParams($signArr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key="."7c4a8d09ca3762af61e59520943AB26Q";
        //签名步骤三：MD5加密或者HMAC-SHA256
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $sign = strtoupper($string);
        //组装xml数据
        $xml = '<xml>
           <appid>'.$appid.'</appid>
           <body>'.$body.'</body>
           <mch_id>'.$mch_id.'</mch_id>
           <nonce_str>'.$nonce_str.'</nonce_str>
           <notify_url>'.$notify_url.'</notify_url>
           <out_trade_no>'.$out_trade_no.'</out_trade_no>
           <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
           <total_fee>'.$total_fee.'</total_fee>
           <trade_type>'.$trade_type.'</trade_type>
           <sign>'.$sign.'</sign>
        </xml>';

        //微信支付地址
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //发送post请求 发送xml数据
        $res = Curl::http_post_xml($url,$xml);

        $resObj = simplexml_load_string($res);
        if($resObj->return_code == 'SUCCESS'){
            $code_url = $resObj->code_url;
            echo $code_url;die;
        }
    }

    private function ToUrlParams($signArr)
    {
        $buff = "";
        foreach ($signArr as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    //成功回调的地址

    public function paycode(){
        echo "成功";
    }

}
