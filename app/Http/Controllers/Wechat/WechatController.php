<?php

namespace App\Http\Controllers\Wechat;

use App\Model\UsersModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;


class WechatController extends Controller
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
            $data=openId($FromUserName);//获取用户信息
            if($Event=="subscribe"){
                //用户关注后用户信息入库
                $this->UserDb($FromUserName,$ToUserName,$data);//用户关注/*79*/
            }elseif($Event=="unsubscribe"){
                $this->UserDbNo($FromUserName,$data);
            }
        }elseif($MsgType=="text"){
            $Content = $obj->Content;//获取文字内容
            $arr=[
                "杨","添","雯","赵","泽","宇","张","豪","袁","帅"
            ];//自定义数组
            if(strpos($Content,"天气")){
                //天气接口
                $this->Weacher($FromUserName,$ToUserName,$Content);//天气接口/*117*/
            }elseif($Content=="1"){
                //回复班级全部人员
                $text=implode(",",$arr);
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//回复消息
                echo $xml;exit;
            }elseif($Content=="2"){
                //随机回复一个班级人员
                $count=count($arr);
                $str=rand(0,$count-1);
                $text=$arr[$str];
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);//回复消息
                echo $xml;exit;
            }else{
                //图灵机器人在线聊天
                $this->RoBot($FromUserName,$ToUserName,$Content);//机器人/*105*/
            }

        }
        
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

    /**
     * 用户关注事件
     * @param $FromUserName
     * @param $ToUserName
     * @param $data
     */
    public function UserDb($FromUserName,$ToUserName,$data){
        $first=UsersModel::where(["openid"=>$FromUserName])->first();
        if($first){//用户关注过
            $arr=[
                "status"=>1
            ];//修改数据
            $res=UsersModel::where(['openid' => $FromUserName])->update($arr);//执行sql
            if($res) {
                $text = "欢迎回来" . $data['nickname'];
                $xml = $this->ReturnText($FromUserName, $ToUserName, $text);
                echo $xml;exit;
            }
        }else{//用户之前未关注过
            $array = array(
                "openid" => $data['openid'],//用户id
                "nickname" => $data['nickname'],//用户名称
                "city" => $data['city'],//用户所在城市
                "province" => $data['province'],//用户所在区
                "country" => $data['country'],//用户所在国家
                "headimgurl" => $data['headimgurl'],//用户头像
                "subscribe_time" => $data['subscribe_time'],//用户时间
                "sex" => $data['sex'],//用户性别
                "status"=>1
            );//设置数组形式的数据类型
            $res=UsersModel::insertGetId($array);
            if($res){
                //关注事件回复消息
                $text="欢迎".$data['nickname']."关注老袁头的微信,\n
                 回复1查看老袁头班级所有人名单,\n
                 回复2随机查看一位班级人姓名,\n
                 回复地区+天气查看当地天气情况,\n";
                //用户关注回复消息
                $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
                echo $xml;exit;
            }
        }

    }

    /**
     * 图灵机器人接口
     * @param $FromUserName
     * @param $ToUserName
     * @param $Content
     */
    public function RoBot($FromUserName,$ToUserName,$Content){
        //人工智能接口回复消息
        $url="http://www.tuling123.com/openapi/api?key=8d54c960c1bc4d24b14dcaf61ca1f903&info=$Content";
        $json=file_get_contents($url);
        $arr=json_decode($json,true);//转换数组类型
        $text=$arr['text'];
        //返回无结果
        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
        echo $xml;exit;
    }

    /**
     * 天气接口
     * @param $FromUserName
     * @param $ToUserName
     * @param $Content
     */
    public function Weacher($FromUserName,$ToUserName,$Content){
        //回复天气消息
        $city=mb_substr($Content,0,-3);//截取城市名称
        $url="http://api.k780.com/?app=weather.future&weaid=$city&appkey=42266&sign=d3c845a7c4109cb8ec891171ea641be5&format=json";//调接口
        $json=file_get_contents($url);//获取数据
        $arr=json_decode($json,true);//转换数组类型
        $text="为您分析天气情况\n";//定义空字符串
        foreach($arr['result'] as $key=>$val){
            $text.="日期:".$val['week']." "."温度情况：".$val['temperature']." "."天气状况：".$val['weather']." "."风向：".$val['wind']."风力：".$val['winp']."\n\n";
        }
        $xml=$this->ReturnText($FromUserName,$ToUserName,$text);
        echo $xml;exit;
    }

    /**
     * 取消关注修改数据库
     * @param $FromUserName
     * @param $data
     */
    public function UserDbNo($FromUserName,$data){
        $arr=[
            "status"=>0
        ];//修改数据
        UsersModel::where(['openid' => $FromUserName])->update($arr);//执行sql
        echo "SUCCESS";
    }
}
