<?php

namespace App\Http\Controllers\Login;

use App\Model\WxAdminModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function index(){
        $id=rand(1000,9999);
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $url="$url/login/wechat?id=$id";
        return view("login.login",['data'=>$url]);
    }

    public function loginadd(Request $request){
        $data=$request->input();
        $name=$data['wx_name'];
        $wx_pwd=$data['wx_pwd'];
        $code=$data['code'];
        $datainfo=WxAdminModel::where(['wx_name'=>$name,'wx_pwd'=>$wx_pwd])->first()->toArray();//查数据
        if(empty($datainfo)){
            $arr=[
                "msg"=>2,
                "font"=>"请输入正确的账号密码"
            ];
            return $arr;
        }
        $code_session=Session::get('code');
        if($code!=$code_session){
            $arr=[
                "msg"=>2,
                "font"=>"验证码不正确"
            ];
            return $arr;
        }else{
            $arr=[
                "msg"=>1,
                "font"=>"身份确认成功"
            ];
            return $arr;
        }
    }

    //发送验证码
    public function code(Request $request){
        $data=$request->input();
        $wx_name=$data['wx_name'];//账号
        $wx_pwd=$data['wx_pwd'];//密码
        $datainfo=WxAdminModel::where(['wx_name'=>$wx_name,'wx_pwd'=>$wx_pwd])->first()->toArray();//查数据
        if(empty($datainfo)){
            return "账户或密码错误";
        }//检测账号密码是否正确
        $openid=$datainfo['openid'];//要接受模板消息的用户openid
        $access=getAccessToken();//access_token
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access";
        $rand=rand(1000,9999);//随机数字
        $arr=[
            "touser"=>$openid,
            "template_id"=>"gfUhEMc4E9mbgKyKW8q7he2DPbYRu_jp0hqg6wh1QBI",
            "data"=>[
                "name"=>[
                    "value"=>$rand,
                    "color"=>"#173177"
                ],
            ]
        ];//拼装数据
        $arr=json_encode($arr,JSON_UNESCAPED_UNICODE);//json数据类型
        $json=curlPost($url,$arr);//调用接口
        $json=json_decode($json,true);//转换数组类型
        Session::put("code",$rand);
        if($json['errmsg']=="ok"){//成功返回结果
            $resInfo=[
                "msg"=>1,
                "font"=>"发送验证码成功"
            ];
            return $resInfo;
        }
    }

    //扫码登陆
    public function wechat(Request $request){
        $id=$request->input("id");
        $openid=getOpenId();
        var_dump($openid);
    }

}
