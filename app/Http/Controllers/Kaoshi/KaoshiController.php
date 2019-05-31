<?php

namespace App\Http\Controllers\Kaoshi;

use App\Model\MenuModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KaoshiController extends Controller
{
    /**
     * 首次接入微信
     */
    public function getWechat(){
        echo $_GET['echostr'];//首次接入返回信息
    }

    /**
     * post接入微信
     */
    public function WXEvent()
    {
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
            if($Event=="CLICK"){
                $EventKey = $obj->EventKey;//通过渠道关注返回的key值
                if($EventKey=="kaoshi"){
                    //菜单被点击的次数，添加到数据库，并且后台展示
                    $res=MenuModel::where(['menu_key'=>$EventKey])->increment('menu_dian');
                    if($res){
                        //入库成功，得出结果
                        $text="点击了一次哟";
                        $this->ReturnText($FromUserName,$ToUserName,$text);
                    }
                }
            }elseif($Event=="VIEW"){
                $EventKey = $obj->EventKey;//通过渠道关注返回的key值
                MenuModel::where(['menu_key'=>$EventKey])->increment('menu_dian');
            }
        }

    }

    //回复文字信息
    public function ReturnText($FromUserName,$ToUserName,$text){
        $xml="<xml>
                  <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                  <FromUserName><![CDATA[".$ToUserName."]]></FromUserName>
                  <CreateTime>".time()."</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[$text]]></Content>
                </xml>";
        return $xml;
    }
}
