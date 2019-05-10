<?php

namespace App\Http\Controllers\Love;

use App\Model\ActModel;
use App\Model\LoveModel;
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
        $Content = $obj->Content;//获取时间类型
            if($Event=="CLICK"){
                $EventKey=$obj->EventKey;
                    //查表白返回数据
                    if($EventKey=="select"){
                        $text="请输入要查询的表白人名字";
                        $name="查表白";
                        $this->upEvent($FromUserName,$name);
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                        echo $xml;exit;
                    }
                    //发表白返回数据
                    if($EventKey=="send"){
                        $text="请输入要发送的表白";
                        $name="发表白";
                        $this->upEvent($FromUserName,$name);
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                        echo $xml;exit;
                    }


            }
        if($MsgType=="text"){
            //看上一步的事件
            $data=$this->allEvent($FromUserName);//查看上一步事件
            $datainfo=json_decode($data,true);
            if($datainfo['act_name']=="发表白"){
                $text="请选择要表白的内容";
                $name="发内容";
                $this->upEvent($FromUserName,$name);//存上一步事件
                /*把表白的具体内容存入数据库*/
                $arr=[
                    'love_openid'=>$FromUserName,
                    'love_name'=>$Content,
                    'love_content'=>"",
                    "love_time"=>time()
                ];
                $res=LoveModel::insertGetId($arr);
                var_dump($res);
                /*把表白的具体内容存入数据库*/
                if($res){
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//提示用户发送表白内容
                    echo $xml;exit;
                }
            }elseif($datainfo['act_name']=="发内容"){
                $arr=[
                    "love_content"=>$Content
                ];
                /*把表白的内容入库，然后提示用户表白成功*/
                $res=LoveModel::where(['love_openid'=>$FromUserName])->update($arr);
                /*把表白的内容入库，然后提示用户表白成功*/
                if($res){
                    $text2="表白成功";
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$text2);//提示用户发送表白内容
                    echo $xml;exit;
                }
            }elseif($datainfo['act_name']=="查表白"){
                $res=LoveModel::where(['love_name'=>$Content])->first();
                $res=json_decode($res,true);//转数组
                $love_name=$res['love_name'];//被表白的人
                $love_content=$res['love_content'];//表白的内容
                $open=openId($FromUserName);
                $nickname=$open["nickname"];
                if($res){
                    $str="表白："."$love_name"."\n"."表白内容："."$love_content"."\n"."表白人："."$nickname";
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$str);//提示用户发送表白内容
                    echo $xml;exit;
                }else{
                    $test="没人跟表白";
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$test);//提示用户发送表白内容
                    echo $xml;exit;
                }
            }

        }
    }

    //记录上步事件
    public function upEvent($FromUserName,$send){
        $arr=[
            "act_openid"=>$FromUserName,
            "act_name"=>$send,
            "act_time"=>time()
            ];
        ActModel::insertGetId($arr);
    }

    //查取上一步事件
    public function allEvent($FromUserName){
        $data=ActModel::where(["act_openid"=>$FromUserName])->orderBy('act_id','desc')->first();
        return $data;
    }

    /**
     * 回复文本消息
     * @param $FromUserName
     * @param $ToUserName
     * @param $text
     * @return string
     */
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
