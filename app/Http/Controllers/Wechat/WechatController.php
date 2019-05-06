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
        echo "SUCCESS";
    }
}
