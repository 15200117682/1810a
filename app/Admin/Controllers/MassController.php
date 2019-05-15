<?php

namespace App\Admin\Controllers;

use App\Model\ImgModel;
use App\Http\Controllers\Controller;
use App\Model\UsersModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class MassController extends Controller
{
    use HasResourceActions;

    /**
     * 群发页面
     */
    public function MassAll(Content $content)
    {
//        echo 111;exit;
        $access_token =getAccessToken();//获取access_token
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=';//获取用户列表
        $data = json_decode(file_get_contents($url), true);//file_get_contents

        $data = $data['data']['openid'];//获取所有openid
        return $content
            ->header('微信')
            ->description('群发列表')
            ->body(view('mass.mass')->with('data', $data));
    }

    /**
     * 执行群发
     */
    public function MassAllAdd()
    {
        $openid = $_POST['openid'];//接受openid
        $media_id = $_POST['media_id'];//接受发送的文本
        $type = $_POST['type'];//接受类型
        $access_token =getAccessToken();//access_token
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access_token";//调接口
        if ($type == "text") {
            $data = [
                "touser" => $openid,
                "msgtype" => "text",
                "text" => [
                    "content" => "$media_id"
                ]
            ];
        }//如果是文本，组合数据
        /*else if ($type == "mpnews") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else if ($type == "mpvideo") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id",
                    "title" => "TITLE",
                    "description" => "DESCRIPTION"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type"
            ];
        }*/
        $data=json_encode($data,true);//转换json数据
        $data=curlPost($url,$data);//请求接口
        $data=json_decode($data,true);//返回数据转数组类型
        if ($data['errcode'] == 0) {//正确返回结果
            return 'ok';
        }
    }

}