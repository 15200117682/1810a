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
                $EventKey=$obj->EventKey;//获取事件信息
                    //查表白返回数据
                    if($EventKey=="select"){//查询菜单点击
                        $text="请输入要查询的表白人名字";
                        $name="查表白";
                        $this->upEvent($FromUserName,$name);//存入事件
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//返回给用户文字提示{调用的函数}
                        echo $xml;exit;
                    }
                    //发表白返回数据
                    if($EventKey=="send"){//发送表白菜单点击
                        $text="请输入要发送表白人的名字";
                        $name="发表白";
                        $this->upEvent($FromUserName,$name);//存入事件
                        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//返回给用户文字提示{调用的函数}
                        echo $xml;exit;
                    }


            }
        if($MsgType=="text"){
            //看上一步的事件
            $data=$this->allEvent($FromUserName);//查看上一步事件
            $datainfo=json_decode($data,true);
            if($datainfo['act_name']=="发表白"){
                $text="请填写要表白的内容";
                $name="发内容";
                $this->upEvent($FromUserName,$name);//存上一步事件
                /*把表白的具体内容存入数据库*/
                $arr=[
                    'love_openid'=>$FromUserName,
                    'love_name'=>$Content,
                    'love_content'=>"",
                    "love_time"=>time()
                ];//先把被表白的人名字入库
                $res=LoveModel::insertGetId($arr);//入库
                /*把表白的具体内容存入数据库*/
                if($res){
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//提示用户发送表白内容
                    echo $xml;exit;
                }
            }elseif($datainfo['act_name']=="发内容"){
                $arr=[
                    "love_content"=>$Content
                ];//存入表白的内容
                /*把表白的内容入库，然后提示用户表白成功*/
                $resdata=LoveModel::where(['love_openid'=>"$FromUserName"])->orderBy("love_id","desc")->first()->toArray();
                $resdata=$resdata['love_id'];
                $res=LoveModel::where(['love_openid'=>$FromUserName])->where(["love_id"=>$resdata])->update($arr);//入库
                /*把表白的内容入库，然后提示用户表白成功*/
                if($res){
                    $text2="表白成功";
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$text2);//提示用户发送表白内容
                    echo $xml;exit;
                }
            }elseif($datainfo['act_name']=="查表白"){
                /************查询表白**************/
                $res=LoveModel::where(['love_name'=>$Content])->first();//查询被表白的人
                $res=json_decode($res,true);//转数组
                $love_name=$res['love_name'];//被表白的人
                $count=LoveModel::where(['love_name'=>$Content])->get()->count();//被表白了多少次
                /*返回查询结果*/
                if($res){
                    $str="表白："."$love_name"."\n"."被表白："."$count"."次";//回复给用户的消息
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$str);//提示用户发送表白内容
                    echo $xml;exit;
                }else{
                    $test="没人跟表白";
                    $xml=$this->ReturnText($FromUserName,$ToUserName,$test);//提示用户发送表白内容
                    echo $xml;exit;
                }
                /*返回查询结果*/
                /************查询表白**************/
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
