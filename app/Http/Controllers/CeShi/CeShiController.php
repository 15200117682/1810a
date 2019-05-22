<?php

namespace App\Http\Controllers\CeShi;

use App\Model\GoodsModel;
use App\Model\TagModel;
use App\Model\WxAdminModel;
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
        //echo "SUCCESS";
    }

    //创建标签
    public function biaoqian(Request $request){
        $name=$request->input();
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$access";
        $datainfo=file_get_contents($url);
        $datainfo=json_decode($datainfo,true);//获取所有标签
        foreach($datainfo as $key=>$value){
            foreach($value as $k=>$v){
                if($v['name']==$name){//如果标签名微信已经有，则无法添加
                    return "标签已经存在，无法新建";
                }else{//否则添加标签
                    $url2="https://api.weixin.qq.com/cgi-bin/tags/create?access_token=$access";
                    $data=[];
                    $data['tag']['name']=$name['name'];
                    $data=json_encode($data,JSON_UNESCAPED_UNICODE);
                    $json=curlPost($url2,$data);
                    $json=json_decode($json,true);
                    $id=$json['tag']['id'];
                    $name=$json['tag']['name'];
                    $arr=[
                        'tag_name'=>$name,
                        'tag_wx_id'=>$id
                    ];
                    $res=TagModel::insertGetId($arr);
                    if($res){
                        return "添加标签成功";
                    }
                }
            }
        }

    }

    //获取access_token
    public function AccessToken(){
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
        $access=$this->AccessToken();
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
        json_decode($json,true);
    }

    public function auth(){
        $openid=session("openid");
        if(empty($openid)){
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            $redirect_uri="$url/ceshi/authpage";
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=". env('WX_APPID')."&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
            header("location:".$url);
        }

        return view("ceshi.button");
    }

    //授权跳转页面
    public function authpage(Request $request){
        $code=$request->input('code');//获取code
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=". env('WX_APPID')."&secret=". env('WX_APPSECRET')."&code=$code&grant_type=authorization_code";//接口
        $data=file_get_contents($url);//使用接口
        $data=json_decode($data,true);//转数组
        $openid=$data['openid'];//openid
        session(['openid'=>$openid]);//session

        return redirect("/ceshi/auth");
    }

    public function button(Request $request){
        $openid=session('openid');
        $data=$request->input();
        $wx_name=$data['wx_name'];
        $wx_pwd=$data['wx_pwd'];
        $where=[
            "wx_name"=>$wx_name,
            "wx_pwd"=>$wx_pwd
        ];
        $update=[
            "openid"=>$openid
        ];
        $res=WxAdminModel::where($where)->update($update);
        var_dump($res);exit;
        if($res){
            return "绑定成功";
        }
    }

}
