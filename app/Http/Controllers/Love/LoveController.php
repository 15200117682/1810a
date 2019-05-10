<?php

namespace App\Http\Controllers\Love;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoveController extends Controller
{
    public function getWechat(){
        echo $_GET['echostr'];//首次接入返回信息
    }
    public function WXEvent()
    {
        $data = file_get_contents("php://input");//通过流的方式接受post数据
        $time = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";//存入时间
        file_put_contents("logs/love_event.log", $time, FILE_APPEND);//存到public日志文件
        $obj = simplexml_load_string($data);//将xml数据转换成对象格式的数据

        $ToUserName = $obj->ToUserName;//获取开发者微信号
        $FromUserName = $obj->FromUserName;//获取用户id（openid）
        $CreateTime = $obj->CreateTime;//获取时间
        $MsgType = $obj->MsgType;//获取数据类型
        $Event = $obj->Event;//获取时间类型
            if($Event=="CLICK"){
                $EventKey=$obj->EventKey;
                    //查表白返回数据
                    if($EventKey=="select"){
                        $text="请输入要查询的表白";
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                        echo $xml;exit;
                    }
                    //发表白返回数据
                    if($EventKey=="send"){
                        $text="请输入要发送的表白";
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                        echo $xml;exit;
                    }
                    //监听上一步的事件 ，1、（select查询数据库，表白） 2、（send把要表白的存入库）

            }
    }

}
