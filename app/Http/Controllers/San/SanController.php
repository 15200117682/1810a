<?php

namespace App\Http\Controllers\San;

use App\Model\EvilModel;
use App\Model\SanModel;
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
        $EventKey=$obj->EventKey;
        $Content = $obj->Content;//获取文字内容
        if($MsgType=="event"){
            if($Event=="subscribe"){
                $text="回答问题哟！！！";
                $xml="<xml>
                  <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                  <FromUserName><![CDATA[".$ToUserName."]]></FromUserName>
                  <CreateTime>".time()."</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[$text]]></Content>
                </xml>";
                return $xml;
            }elseif($Event=="CLICK"){
                if($EventKey=="da"){
                    //答题  1、去数据库查取一条数据返回
                    $data=SanModel::orderByRaw("RAND()")->first()->toArray();
                    $text=$data['wx_name']."回答A是".$data['wx_a']."or回答B是".$data['wx_b'];
                    $xml="<xml>
                  <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                  <FromUserName><![CDATA[".$ToUserName."]]></FromUserName>
                  <CreateTime>".time()."</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[$text]]></Content>
                </xml>";
                    return $xml;
                }elseif($EventKey=="cheng"){
                    //查询FormUserName的成绩单
                    $nickname=openId($FromUserName);//获取用户信息
                    $data=EvilModel::where(['openid'=>$FromUserName])->first();
                    $text="您好".$nickname['nickname']."。"."您回答正确".$data['wx_cor']."。"."回答错误".$data['wx_cor_no'];
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
        }

    }
}
