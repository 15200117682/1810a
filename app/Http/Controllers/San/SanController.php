<?php

namespace App\Http\Controllers\San;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SanController extends Controller
{
    //首次接入
    public function getSan(){
        echo $_GET["echostr"];
    }

    //之后接入
    public function getEvent()
    {
        $data = file_get_contents("php://input");//接受post数据
        $time = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";//存时间
        file_put_contents("logs/san_event.log", $time, FILE_APPEND);//存到日志文件
        $obj = simplexml_load_string($data);//将xml数据转换成对象格式的数据
        $ToUserName = $obj->ToUserName;//获取开发者微信号
        $FromUserName = $obj->FromUserName;//获取用户id（openid）
        $CreateTime = $obj->CreateTime;//获取时间
        $MsgType = $obj->MsgType;//获取数据类型
        $Event = $obj->Event;//获取时间类型
        $Content = $obj->Content;//获取文字内容
        echo "SUCCESS";
    }
}
