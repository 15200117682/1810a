<?php

namespace App\Http\Controllers\CeShi;

use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class CeShiController extends Controller
{

    //首次接入
    public function getCe(){
        echo $_GET["echostr"];
    }

    //之后接入
    public function getWechat(){
        $data = file_get_contents("php://input");//接受post数据
        $time = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";//存时间
        file_put_contents("logs/ce_event.log", $time, FILE_APPEND);//存到日志文件
        $obj = simplexml_load_string($data);//将xml数据转换成对象格式的数据
        $ToUserName = $obj->ToUserName;//获取开发者微信号
        $FromUserName = $obj->FromUserName;//获取用户id（openid）
        $CreateTime = $obj->CreateTime;//获取时间
        $MsgType = $obj->MsgType;//获取数据类型
        $Event = $obj->Event;//获取时间类型
        $Content = $obj->Content;//获取文字内容
        if($MsgType=="event"){
            if($Event=="subscribe"){
                $text="请输入商品名字";
                $xml="<xml>
                  <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                  <FromUserName><![CDATA[".$ToUserName."]]></FromUserName>
                  <CreateTime>".time()."</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[$text]]></Content>
                </xml>";
                return $xml;
            }
        }elseif($MsgType=="text"){
            $this->userText($FromUserName,$Content);
        }
        echo "SUCCESS";
    }

    //获取access_token
    public function AccesToken(){
        $key="access_token";
        $token=Redis::get("access_token");
        if($token){

        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . env('WX_APPID') . "&secret=" . env('WX_APPSECRET');//调接口
            $response = file_get_contents($url);
            $arr = json_decode($response, true);
            Redis::set($key, $arr['access_token']);// 存缓存
            Redis::expire($key, 3600);
            $token = $arr['access_token'];
        }
        return $token;
    }
    
    //用户回复
    public function userText($FromUserName,$Content){
        $data=GoodsModel::where(['goods_name'=>$Content])->first();//随机查询一条数据
        $data=json_decode($data,true);
        $access=$this->AccesToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access";
        $arr=[
            "touser"=>(string)$FromUserName,
            "template_id"=>"S1Cfgg1rNM2E9FlwFX6dTOaFcdHO7L9ESO1VXhJrzBg",
            "data"=>[
                "first"=>[
                        "value"=>"为您找到商品！",
                       "color"=>"#173177"
                   ],
                   "name"=>[
                        "value"=>$data['goods_name'],
                       "color"=>"#173177"
                   ],
                "price"=>[
                    "value"=>$data['goods_price'],
                    "color"=>"#173177"
                ],
                "srcoe"=>[
                    "value"=>$data['goods_srcoe'],
                    "color"=>"#173177"
                ],
            ]
        ];
        $arr=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json=curlPost($url,$arr);
        $json=json_decode($json,true);
    }
}
