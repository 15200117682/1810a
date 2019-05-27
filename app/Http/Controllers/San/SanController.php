<?php

namespace App\Http\Controllers\San;

use App\Model\EvilModel;
use App\Model\NameModel;
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
        if($MsgType=="event"){
            if($Event=="subscribe"){//用户关注
                $text="回答问题哟！！！";
                $xml=$this->returnText($FromUserName,$ToUserName,$text);
                return $xml;
            }elseif($Event=="CLICK"){//用户点击菜单

                if($EventKey=="da"){//点击回答问题
                    //答题  1、去数据库查取一条数据返回
                    $data=SanModel::orderByRaw("RAND()")->first()->toArray();//查询题目
                    $text=$data['wx_name']."回答A是".$data['wx_a']."or回答B是".$data['wx_b'];//让用户回答问题
                    $arr=[
                        'id_name'=>$FromUserName,
                        'id_openid'=>$data['id'],
                        'id_time'=>time()
                    ];
                    NameModel::insertGetId($arr);
                    $xml=$this->returnText($FromUserName,$ToUserName,$text);//返回问题
                    return $xml;

                }elseif($EventKey=="cheng"){//点击查询成绩单
                    $nickname=openId($FromUserName);//获取用户信息
                    $data=EvilModel::where(['openid'=>$FromUserName])->first();//根据用户openid查询自己的成绩
                    $text="您好".$nickname['nickname']."。"."您回答正确".$data['wx_cor']."。"."回答错误".$data['wx_cor_no'];//拼装数据
                    $xml=$this->returnText($FromUserName,$ToUserName,$text);//返回文字信息
                    return $xml;
                }
            }
        }elseif($MsgType=="text"){
            $Content = $obj->Content;//获取文字内容
            $data=NameModel::where(["id_openid"=>$FromUserName])->orderBy('id','desc')->first();
            $wx_cor=SanModel::where(['id'=>$data['id_name']])->pluck("wx_cor")->first();
            if($Content==$wx_cor){
                EvilModel::where(['openid'=>$FromUserName])->increment("wx_cor");
                $text="恭喜，回答正确";
                $xml=$this->returnText($FromUserName,$ToUserName,$text);//返回文字信息
                return $xml;
            }else{
                EvilModel::where(['openid'=>$FromUserName])->increment("wx_cor_no");
                $text="对不起，回答错误";
                $xml=$this->returnText($FromUserName,$ToUserName,$text);//返回文字信息
                return $xml;
            }
        }

    }

    //回复用户文字消息
    public function returnText($FromUserName,$ToUserName,$text){
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
