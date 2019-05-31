<?php

namespace App\Http\Controllers\Wechat;

use App\Model\CodeModel;
use App\Model\ImgModel;
use App\Model\MenuModel;
use App\Model\UsersModel;
use App\Model\ActModel;
use App\Model\LoveModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

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

        $EventKey=$obj->EventKey;//通过渠道关注返回的key值
        if($MsgType=="event"){
            $data=openId($FromUserName);//获取用户信息
            if($Event=="subscribe"){
                //用户关注后用户信息入库
                $this->UserDb($FromUserName,$ToUserName,$data,$EventKey);//用户关注/*79*/
            }elseif($Event=="unsubscribe"){
                $this->UserDbNo($FromUserName);
            }elseif($Event=="CLICK"){
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
        }elseif($MsgType=="image"){
            $this->doutu($FromUserName,$ToUserName);

        }elseif($MsgType=="text"){
            $Content = $obj->Content;//获取文字内容
            $data=$this->allEvent($FromUserName);//查看上一步事件

            if($data && (time()-$data['act_time'] < 60)){
                $this->biaobai($FromUserName,$ToUserName,$data,$Content);
            }else{
                $this->text($FromUserName,$ToUserName,$Content);
            }

        }
        
    }

    //表白
    public function biaobai($FromUserName,$ToUserName,$datainfo,$Content){
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

    //斗图
    public function doutu($FromUserName,$ToUserName){
        $data=ImgModel::orderByRaw("RAND()")->first();//随机查询一条数据
        $media_id=$data->img_media;
        if($media_id){
            $xml="<xml>
                            <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                            <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                            <CreateTime>".time()."</CreateTime>
                            <MsgType><![CDATA[image]]></MsgType>
                            <Image>
                            <MediaId><![CDATA[$media_id]]></MediaId>
                            </Image>
                        </xml>";
            echo $xml;
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

    //自己写的文本回复
    public function text($FromUserName,$ToUserName,$Content){
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
     * 新增临时素材
     */
    public function materadd(){
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access&type=image";
        $image="/wwwroot/1810a/public/img/116.jpg";
        $imgPath = new \CURLFile($image); //通过CURLFile处理
        $post_data = [
            'media'=>$imgPath  //素材路径
        ];
        $res = curlPost($url,$post_data);
        var_dump($res);die;
    }


    public function menu1(){
        $access=getAccessToken();
        $arr=MenuModel::all()->toArray();
        $typeArr = ['click'=>'key','view'=>'url'];//数组类型
        //var_dump($arr);exit;
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access";
        $data = [];//设置自定义菜单参数
        foreach($arr as $key=>$value){
                $data['button'][] = [
                    'type' => $value['menu_type'],
                    'name' => $value['menu_name'],
                    $typeArr[$value['menu_type']] => $value['menu_key']
                ];
        }
        //var_dump($data);exit;
        $json=json_encode($data,JSON_UNESCAPED_UNICODE);//转换数据类型
        //var_dump($json);exit;
        $res = curlPost($url,$json);
        echo $res;exit;
    }

    //二级菜单创建
    public function menu(){
        $datanull=MenuModel::all();
        $access=getAccessToken();//获取access_token
        if($datanull==""){
            $url="https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=$access";
            $res=file_get_contents($url);
            $errmsg=json_decode($res,true);
            if($errmsg['errmag']=="ok"){
                return redirect('/admin/menulist');
            }
        }//调用接口删除菜单
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access";//调接口
        $arr=MenuModel::where(['p_id'=>0])->get()->toArray();//查询一级菜单
        $typeArr = ['click'=>'key','view'=>'url','pic_weixin'=>'key'];//数组类型
        $data=[];//定义空数组
        foreach($arr as $key=>$value){
            if(empty($value['menu_type'])&&empty($value['menu_key'])){//有二级菜单的
                $data['button'][$key]['name'] = $value['menu_name'];//二级菜单的名字
                $dataInfo = MenuModel::where(['p_id'=>$value['id']])->get()->toArray();
                foreach ($dataInfo as $k => $v) {
                    $data['button'][$key]['sub_button'][] = [
                        'type'=> $v['menu_type'],
                        'name'=> $v['menu_name'],
                        $typeArr[$v['menu_type']] => $v['menu_key']
                    ];
                }
            }else{//没有二级菜单的
                $data['button'][] = [
                    'type'=> $value['menu_type'],
                    'name'=> $value['menu_name'],
                    $typeArr[$value['menu_type']] => $value['menu_key']
                ];//组成数组
            }
        }
        //var_dump($data);exit;
        $json=json_encode($data,JSON_UNESCAPED_UNICODE);//转换数据类型
        //var_dump($json);exit;
        $res = curlPost($url,$json);
        var_dump($res);exit;
    }

    /**
     * 获取临时素材
     */
    public function materlist(){
        $access=getAccessToken();
        $media_id="B94KK6tnr0JpepcIAFO2vz7WI3WB3rJkx9IQvQAVgtT2BpzNu9aog488HAnLlfif";
        $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$media_id";
        $json=file_get_contents($url);
        var_dump($json);
    }

    /**
     * 用户关注事件
     * @param $FromUserName
     * @param $ToUserName
     * @param $data
     */
    public function UserDb($FromUserName,$ToUserName,$data,$EventKey){
        $first=UsersModel::where(["openid"=>$FromUserName])->first();
        if($first){//用户关注过
            $arr=[
                "status"=>1
            ];//修改数据
            $key=substr($EventKey,8);
            CodeModel::where(['code_key'=>$key])->increment("code_number");
            $res=UsersModel::where(['openid' => $FromUserName])->update($arr);//执行sql
            if($res) {
                $text = "欢迎回来" . $data['nickname'];
                $xml = $this->ReturnText($FromUserName, $ToUserName, $text);
                echo $xml;exit;
            }
        }else{//用户之前未关注过
            if($EventKey==""){
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
            }else{
                $key=substr($EventKey,8);
                $array = array(
                    "openid" => $data['openid'],//用户id
                    "nickname" => $data['nickname'],//用户名称
                    "city" => $data['city'],//用户所在城市
                    "province" => $data['province'],//用户所在区
                    "country" => $data['country'],//用户所在国家
                    "headimgurl" => $data['headimgurl'],//用户头像
                    "subscribe_time" => $data['subscribe_time'],//用户时间
                    "sex" => $data['sex'],//用户性别
                    "status"=>1,//状态
                    "code_key"=>$key//code_key
                );//设置数组形式的数据类型
                //把渠道表关注人数改变
                CodeModel::where(['code_key'=>$key])->increment("code_number");
            }

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
    public function UserDbNo($FromUserName){
        $arr=[
            "status"=>0
        ];//修改数据
        $one=UsersModel::where(['openid' => $FromUserName])->first();//执行sql
        $number=$one['code_key'];
        CodeModel::where(['code_key'=>$number])->decrement("code_number");
        UsersModel::where(['openid' => $FromUserName])->update($arr);//执行sql
        echo "SUCCESS";
    }

    //获取用户列表
    public function userList(){
        exit;
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access&next_openid=";
        $dataOpenId=file_get_contents($url);
        $dataOpenId=json_decode($dataOpenId,true);
        $openid=$dataOpenId['data']['openid'];
        foreach($openid as $key=>$value){
            $data=openId($value);
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
        }
        if($res){
            echo "添加用户信息入库成功";
        }
    }

}
