<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WechatController extends Controller
{

    //首次接入微信
    public function getWechat(){
        echo $_GET['echostr'];//首次接入返回信息
    }

    //post接入微信
    public function WXEvent(){
        $data = file_get_contents("php://input");//通过流的方式接受post数据
        $time = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";//存入时间
        file_put_contents("logs/wx_event.log", $time, FILE_APPEND);//存到public日志文件
        $obj = simplexml_load_string($data);//将xml数据转换成对象格式的数据

        $ToUserName = $obj->ToUserName;//获取开发者微信号
        $FromUserName = $obj->FromUserName;//获取用户id（openid）
        $CreateTime = $obj->CreateTime;//获取时间
        $MsgType = $obj->MsgType;//获取数据类型
        $Event = $obj->Event;//获取时间类型
        if($MsgType=="event"){
            if($Event=="subscribe"){
                //用户关注回复消息
                $xml='
                    <xml>
              <ToUserName><![CDATA['.$FromUserName.']]></ToUserName>
              <FromUserName><![CDATA['.$ToUserName.']]></FromUserName>
              <CreateTime>.time().</CreateTime>
              <MsgType><![CDATA[text]]></MsgType>
              <Content><![CDATA[欢迎订阅老袁头的公众号]]></Content>
            <xml>';
                echo $xml;
            }
        }else if($MsgType=="text"){
            $Content = $obj->Content;//获取文本消息内容
            if(strpos($Content,"+天气")){
                //回复天气情况
            }
        }

        echo "SUCCESS";
    }
}
