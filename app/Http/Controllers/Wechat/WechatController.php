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
                $text="欢迎关注老袁头的微信,\n
                 回复1查看老袁头班级所有人名单,\n
                 回复2随机查看一位班级人姓名,\n
                 回复天气查看当地天气情况,\n";
                //用户关注回复消息
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
            }
        }elseif($MsgType=="text"){
            $Content = $obj->Content;//获取文字内容
            $arr=[
                "杨","添","雯","赵","泽","宇","张","豪","袁","帅"
            ];
            if(strpos($Content,"+天气")){
                //回复天气消息
                $city=mb_substr($Content,0,2);//截取城市名称
                $url="https://free-api.heweather.net/s6/weather/now?key=HE1904161039381186&location=$city";//调接口
                $json=file_get_contents($url);//获取数据
                $arr=json_decode($json,true);//转换数据类型
                var_dump($arr);exit;
            }elseif($Content=="1"){
                //回复班级全部人员
                $text=implode(",",$arr);
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
            }elseif($Content=="2"){
                //随机回复一个班级人员
                $count=count($arr);
                $str=rand(0,$count-1);
                $text=$arr[$str];
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
            }else{
                //返回无结果
                $text="抱歉，目前暂时无法为您找到相关的服务";
            }
        }
        
    }

    //回复文本消息
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
