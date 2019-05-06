<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;


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
                $url="http://api.k780.com/?app=weather.future&weaid=$city&appkey=42266&sign=d3c845a7c4109cb8ec891171ea641be5&format=json";//调接口
                $json=file_get_contents($url);//获取数据
                $arr=json_decode($json,true);//转换数组类型
                $text="为你分析天气情况\n";//定义空字符串
                foreach($arr['result'] as $key=>$val){
                    $text.="日期:".$val['week']." "."温度情况：".$val['temperature']." "."天气状况：".$val['weather']." "."风向：".$val['wind']."风力：".$val['winp']."\n\n";
                }
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
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
                $text="抱歉，目前暂时无法为您找到相关的服务，\n正在努力帮你联系后台相关工作人员";
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
            }
        }
        
    }

    //获取access_token
    public function getAccessToken(){
        $key = 'access_token';
        $token = Redis::get($key);
        if($token){
            //有缓存返回缓存数据
        }else{
            //无缓存调用接口获取access_token
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET');//调接口
            $response = file_get_contents($url);
            $arr = json_decode($response,true);//转换为数组
            Redis::set($key,$arr['access_token']);// 存缓存
            Redis::expire($key,3600);
            $token = $arr['access_token'];
        }
        return $token;
    }

    //获取用户基本信息
    public function openId($openId){
        //获取access
        $access=$this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$openId&lang=zh_CN";
        $count=file_get_contents($url);//流接受数据
        $u=json_decode($count,true);//转换数据为数组类型
        return $u;//返回数据
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
